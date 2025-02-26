<?php

namespace App\Traits;

trait IsReadOnly
{
    protected bool $isReadOnly = false;

    public function makeReadOnly(): void
    {
        $this->isReadOnly = true;
    }

    public function save(array $options = [])
    {
        if ($this->isReadOnly) {
            throw new \RuntimeException('Cannot modify historical data');
        }
        return parent::save($options);
    }

    public function delete()
    {
        if ($this->isReadOnly) {
            throw new \RuntimeException('Cannot modify historical data');
        }
        return parent::delete();
    }

    public function update(array $attributes = [])
    {
        if ($this->isReadOnly) {
            throw new \RuntimeException('Cannot modify historical data');
        }
        return parent::update($attributes);
    }
} 