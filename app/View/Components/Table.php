<?php

namespace App\View\Components;

use Livewire\Component;

class Table extends Component {

    public function __construct(
        public string $table
    ) {}

    public function render() {
        return view('components.tables.table');
    }
}