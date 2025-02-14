<?php

namespace App\Tables;

abstract class Table {

    protected string $model;

    abstract public function columns(): array;

    public function query()
    {
        return $this->model::query();
    }

    public function getUrl()
    {
        return null;
    }

}