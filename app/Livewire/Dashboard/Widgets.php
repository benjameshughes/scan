<?php

namespace App\Livewire\Dashboard;

use App\Models\Scan;
use Illuminate\Support\Collection;
use Livewire\Component;

class Widgets extends Component
{
    public Collection $scans;

    public function mount()
    {
        $this->scans = Scan::all();
    }
    public function render()
    {
        return view('livewire.dashboard.widgets');
    }
}
