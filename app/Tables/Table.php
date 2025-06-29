<?php

namespace App\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Table
{
    protected $query;

    protected array $columns = [];

    protected array $searchableColumns = [];

    protected array $filters = [];

    protected array $actions = [];

    protected array $bulkActions = [];

    protected string $defaultSortField = 'id';

    protected string $defaultSortDirection = 'asc';

    protected ?string $model = null;

    protected bool $exportable = false;

    protected array $exportFormats = ['csv'];

    protected ?string $createRoute = null;

    protected ?string $editRoute = null;

    protected ?string $viewRoute = null;

    protected ?string $deleteAction = null;

    protected bool $selectable = false;

    protected int $perPage = 10;

    protected array $perPageOptions = [10, 25, 50, 100];

    public static function make(): self
    {
        return new static;
    }

    public function query(\Closure $callback): self
    {
        $this->query = $callback;

        return $this;
    }

    public function model(string|Model $model): self
    {
        $this->model = is_string($model) ? $model : get_class($model);

        return $this;
    }

    public function columns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function searchable(array $columns): self
    {
        $this->searchableColumns = $columns;

        return $this;
    }

    public function filters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function actions(array $actions): self
    {
        $this->actions = $actions;

        return $this;
    }

    public function bulkActions(array $bulkActions): self
    {
        $this->bulkActions = $bulkActions;
        $this->selectable = true;

        return $this;
    }

    public function exportable(array $formats = ['csv']): self
    {
        $this->exportable = true;
        $this->exportFormats = $formats;

        return $this;
    }

    public function crud(?string $createRoute = null, ?string $editRoute = null, ?string $viewRoute = null, ?string $deleteAction = null): self
    {
        $this->createRoute = $createRoute;
        $this->editRoute = $editRoute;
        $this->viewRoute = $viewRoute;
        $this->deleteAction = $deleteAction;
        $this->selectable = ! empty($deleteAction);

        return $this;
    }

    public function selectable(bool $selectable = true): self
    {
        $this->selectable = $selectable;

        return $this;
    }

    public function perPage(int $perPage, ?array $options = null): self
    {
        $this->perPage = $perPage;
        if ($options !== null) {
            $this->perPageOptions = $options;
        }

        return $this;
    }

    public function defaultSort(string $field, string $direction = 'asc'): self
    {
        $this->defaultSortField = $field;
        $this->defaultSortDirection = $direction;

        return $this;
    }

    // Getters
    public function getQuery(): Builder
    {
        if ($this->query) {
            return call_user_func($this->query);
        }

        if ($this->model) {
            return $this->model::query();
        }

        throw new \Exception('No query or model defined for table');
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getBulkActions(): array
    {
        return $this->bulkActions;
    }

    public function isExportable(): bool
    {
        return $this->exportable;
    }

    public function getExportFormats(): array
    {
        return $this->exportFormats;
    }

    public function isSelectable(): bool
    {
        return $this->selectable;
    }

    public function getCreateRoute(): ?string
    {
        return $this->createRoute;
    }

    public function getEditRoute(): ?string
    {
        return $this->editRoute;
    }

    public function getViewRoute(): ?string
    {
        return $this->viewRoute;
    }

    public function getDeleteAction(): ?string
    {
        return $this->deleteAction;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getPerPageOptions(): array
    {
        return $this->perPageOptions;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getDefaultSortField(): string
    {
        return $this->defaultSortField;
    }

    public function getDefaultSortDirection(): string
    {
        return $this->defaultSortDirection;
    }
}
