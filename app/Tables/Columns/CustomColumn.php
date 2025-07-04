<?php

namespace App\Tables\Columns;

class CustomColumn extends Column
{
    protected ?string $viewPath = null;
    
    public function view(string $viewPath): self
    {
        $this->viewPath = $viewPath;
        return $this;
    }
    
    public function getValue($record)
    {
        if ($this->viewPath) {
            return view($this->viewPath, [
                'row' => $record,
                'value' => data_get($record, $this->name),
            ])->render();
        }
        
        return parent::render($record);
    }
    
    public function searchable(): static
    {
        // Custom columns are usually not searchable by default
        return $this;
    }
}