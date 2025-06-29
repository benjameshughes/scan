<?php

namespace App\Tables\Columns;

class DateColumn extends TextColumn
{
    protected string $format = 'Y-m-d H:i:s';

    protected bool $diffForHumans = false;

    public function format(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function diffForHumans(bool $diffForHumans = true): self
    {
        $this->diffForHumans = $diffForHumans;

        return $this;
    }

    public function getValue($record)
    {
        if ($this->valueCallback) {
            return call_user_func($this->valueCallback, $record);
        }

        $value = data_get($record, $this->name);

        if ($value) {
            if ($this->diffForHumans) {
                return $value->diffForHumans();
            }

            return $value->format($this->format);
        }

        return $value;
    }
}
