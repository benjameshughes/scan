<?php

namespace App\Tables;

use App\Tables\Columns\ActionsColumn;
use App\Tables\Columns\BadgeColumn;
use App\Tables\Columns\DateColumn;
use App\Tables\Columns\TextColumn;
use App\Tables\Concerns\HasSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

abstract class TableComponent extends Component
{
    use HasSearch, WithPagination;

    public string $search = '';
    
    protected int $searchMinLength = 2;

    public int $perPage = 10;
    
    public array $perPageOptions = [1, 5, 10, 25, 50, 100, 250];

    public string $sortField = '';

    public string $sortDirection = 'asc';

    public array $selectedRecords = [];

    public array $filters = [];

    public bool $showFilters = false;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showDeleteModal = false;

    public ?Model $editingRecord = null;

    public ?Model $deletingRecord = null;

    public array $bulkSelectedIds = [];

    public bool $selectAll = false;
    
    public bool $isSelectingAll = false;
    
    public int $totalRecordsCount = 0;
    
    public bool $selectAllPages = false;
    
    public array $processingRows = [];

    // Store custom action callbacks by their hash
    protected array $customActionCallbacks = [];

    // Auto-discovery properties
    protected ?string $model = null;

    protected ?string $title = null;

    protected array $searchable = [];

    protected array $fillable = [];

    // Each table can implement this method, or use auto-discovery
    public function table(Table $table): Table
    {
        if ($this->model) {
            $table->model($this->model);
        }

        // Auto-discover columns if not defined
        if (empty($table->getColumns())) {
            $table->columns($this->autoDiscoverColumns());
        }

        // Auto-discover searchable fields
        if (empty($table->getSearchableColumns()) && ! empty($this->searchable)) {
            $table->searchable($this->searchable);
        }

        return $table;
    }

    protected function getTable(): Table
    {
        $table = $this->table(Table::make());
        
        // Set component ID for ActionsColumn instances and register callbacks
        foreach ($table->getColumns() as $column) {
            if ($column instanceof ActionsColumn) {
                $column->setComponentId($this->getId());
                
                // Register custom action callbacks
                foreach ($column->getActions() as $action) {
                    if ($action instanceof \App\Tables\Actions\CustomAction && $action->getActionCallback()) {
                        $actionId = spl_object_hash($action);
                        $this->customActionCallbacks[$actionId] = $action->getActionCallback();
                    }
                }
            }
        }
        
        return $table;
    }

    public function mount(): void
    {
        $table = $this->getTable();

        if (empty($this->sortField)) {
            $this->sortField = $table->getDefaultSortField();
            $this->sortDirection = $table->getDefaultSortDirection();
        }

        // Load saved per-page preference or use default
        $this->perPage = session()->get('table_per_page_' . static::class, $table->getPerPage());
        
        // Initialize total records count
        $this->totalRecordsCount = $this->getQuery()->count();
    }

    // Auto-discovery methods
    protected function autoDiscoverColumns(): array
    {
        if (! $this->model) {
            return [];
        }

        $modelInstance = new $this->model;
        $fillable = $modelInstance->getFillable();
        $columns = [];

        // Always include ID first if it exists
        if ($modelInstance->getKeyName()) {
            $columns[] = TextColumn::make($modelInstance->getKeyName())->sortable();
        }

        foreach ($fillable as $field) {
            if (Str::endsWith($field, '_at')) {
                $columns[] = DateColumn::make($field)->diffForHumans()->sortable();
            } elseif (in_array($field, ['status', 'type', 'state'])) {
                $columns[] = BadgeColumn::make($field)->sortable();
            } elseif (! in_array($field, ['password', 'remember_token', 'email_verified_at'])) {
                $columns[] = TextColumn::make($field)->sortable()->searchable();
            }
        }

        // Add actions column
        $columns[] = ActionsColumn::make('actions')->edit()->delete();

        return $columns;
    }

    protected function getQuery()
    {
        $query = $this->getTable()->getQuery();

        // Apply search if available
        if ($this->hasSearch() && ! empty($this->search)) {
            $query = $this->applySearch($query);
        }

        // Apply filters
        $this->applyFilters($query);

        // Apply sorting
        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query;
    }

    protected function applyFilters($query)
    {
        foreach ($this->getTable()->getFilters() as $filter) {
            $value = $this->filters[$filter['key']] ?? $filter['default'] ?? null;

            if ($value !== null && $value !== '') {
                if (isset($filter['apply']) && is_callable($filter['apply'])) {
                    call_user_func($filter['apply'], $query, $value);
                }
            }
        }
    }

    // Sorting
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }
    
    // Search handling with validation
    public function updatedSearch(): void
    {
        // Security: sanitize search input
        $this->search = trim(strip_tags($this->search));
        
        // Reset page when search changes
        $this->resetPage();
    }
    
    // Per page handling
    public function updatedPerPage(): void
    {
        $this->resetPage();
        
        // Store preference in session
        session()->put('table_per_page_' . static::class, $this->perPage);
    }

    // Bulk selection
    public function updatedSelectAll(): void
    {
        $this->isSelectingAll = true;
        
        if ($this->selectAll) {
            // Get IDs from current page only by default
            $query = $this->getQuery();
            $this->bulkSelectedIds = $query->paginate($this->perPage)->pluck('id')->toArray();
        } else {
            $this->bulkSelectedIds = [];
            $this->selectAllPages = false;
        }
        
        $this->isSelectingAll = false;
    }
    
    public function selectAllAcrossPages(): void
    {
        $this->isSelectingAll = true;
        $this->selectAllPages = true;
        
        // Get all IDs across all pages
        $this->bulkSelectedIds = $this->getQuery()->pluck('id')->toArray();
        $this->selectAll = true;
        
        $this->isSelectingAll = false;
    }
    
    public function clearSelection(): void
    {
        $this->bulkSelectedIds = [];
        $this->selectAll = false;
        $this->selectAllPages = false;
    }

    public function toggleBulkSelect(int $id): void
    {
        if (in_array($id, $this->bulkSelectedIds)) {
            $this->bulkSelectedIds = array_diff($this->bulkSelectedIds, [$id]);
        } else {
            $this->bulkSelectedIds[] = $id;
        }

        // Update selectAll based on current page items
        $currentPageIds = $this->getQuery()->paginate($this->perPage)->pluck('id')->toArray();
        $selectedOnPage = array_intersect($this->bulkSelectedIds, $currentPageIds);
        
        $this->selectAll = count($selectedOnPage) === count($currentPageIds) && count($currentPageIds) > 0;
        
        // Reset select all pages if user manually deselects
        if (count($this->bulkSelectedIds) < count($currentPageIds)) {
            $this->selectAllPages = false;
        }
    }

    // Bulk actions
    public function executeBulkAction(string $action): void
    {
        // Security: validate action name
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $action)) {
            session()->flash('error', 'Invalid action name.');
            return;
        }
        
        if (empty($this->bulkSelectedIds)) {
            session()->flash('error', 'No records selected.');
            return;
        }

        // Security: validate selected IDs are integers
        $validIds = array_filter($this->bulkSelectedIds, 'is_numeric');
        if (count($validIds) !== count($this->bulkSelectedIds)) {
            session()->flash('error', 'Invalid selection.');
            return;
        }

        $bulkActions = collect($this->getTable()->getBulkActions())
            ->keyBy('name');

        if ($bulkActions->has($action)) {
            $bulkAction = $bulkActions->get($action);
            $selectedCount = count($this->bulkSelectedIds);
            
            try {
                // Execute the bulk action
                $result = call_user_func($bulkAction['handle'], $this->bulkSelectedIds);
                
                // Show success message
                $message = $result['message'] ?? "{$selectedCount} " . Str::plural('record', $selectedCount) . " updated successfully.";
                session()->flash('success', $message);
                
                // Clear selection after successful action
                $this->clearSelection();
                
                // Refresh the table data
                $this->resetPage();
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to execute bulk action: ' . $e->getMessage());
            }
        }
    }

    // CRUD operations
    public function create(): void
    {
        $this->editingRecord = new $this->model;
        $this->showCreateModal = true;
    }

    public function edit(int $id): void
    {
        $this->editingRecord = $this->model::find($id);
        $this->showEditModal = true;
    }

    public function delete(int $id): void
    {
        $this->deletingRecord = $this->model::find($id);

        if ($this->deletingRecord && auth()->user()->cannot('delete', $this->deletingRecord)) {
            session()->flash('error', 'You are not authorized to delete this record.');

            return;
        }

        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        if ($this->deletingRecord) {
            $this->authorize('delete', $this->deletingRecord);

            $this->deletingRecord->delete();
            $this->showDeleteModal = false;
            $this->deletingRecord = null;
            session()->flash('message', 'Record deleted successfully.');
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletingRecord = null;
    }

    // Filters
    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function resetFilters(): void
    {
        $this->filters = [];
        $this->resetPage();
    }

    // Execute custom action callback
    public function executeCustomAction(int $recordId, string $actionId): void
    {
        // Security: validate recordId is positive integer
        if ($recordId <= 0) {
            session()->flash('error', 'Invalid record ID.');
            return;
        }
        
        // Security: validate actionId format
        if (!is_string($actionId) || empty($actionId)) {
            session()->flash('error', 'Invalid action ID.');
            return;
        }
        
        if (!isset($this->customActionCallbacks[$actionId])) {
            session()->flash('error', 'Action not found.');
            return;
        }

        $record = $this->model::find($recordId);
        if (!$record) {
            session()->flash('error', 'Record not found.');
            return;
        }

        // Add to processing rows for UI feedback
        $this->processingRows[] = $recordId;

        try {
            $callback = $this->customActionCallbacks[$actionId];
            call_user_func($callback, $record, $this);
            session()->flash('success', 'Action completed successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Action failed: ' . $e->getMessage());
        } finally {
            // Remove from processing rows
            $this->processingRows = array_diff($this->processingRows, [$recordId]);
        }
    }

    // Export
    public function export(string $format = 'csv'): void
    {
        // Implementation depends on your export package
        // This is a placeholder for the export functionality
        session()->flash('message', "Export in {$format} format initiated.");
    }

    public function render()
    {
        $query = $this->getQuery();
        $this->totalRecordsCount = $query->count();
        $data = $query->paginate($this->perPage);

        // Update selectAll state based on current page
        if ($this->selectAll && !$this->selectAllPages) {
            $currentPageIds = $data->pluck('id')->toArray();
            $selectedOnPage = array_intersect($this->bulkSelectedIds, $currentPageIds);
            $this->selectAll = count($selectedOnPage) === count($currentPageIds) && count($currentPageIds) > 0;
        }

        return view('components.tables.enhanced-table', [
            'data' => $data,
            'table' => $this->getTable(),
        ]);
    }
}
