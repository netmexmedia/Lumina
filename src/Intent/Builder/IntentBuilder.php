<?php

namespace Netmex\Lumina\Intent\Builder;

use Netmex\Lumina\Intent\FilterIntentInterface;
use Netmex\Lumina\Intent\Intent;

class IntentBuilder implements IntentBuilderInterface
{
    private string $type;
    private string $field;
    private ?string $strategy = null;
    private ?string $model = null;
    private array $filters = [];

    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function field(string $field): self
    {
        $this->field = $field;
        return $this;
    }

    public function strategy(string $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function model(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function addFilter(FilterIntentInterface $filter): self
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function build(): Intent
    {
//        if ($this->strategy === null) {
//            throw new \LogicException('Intent strategy not defined');
//        }

        $intent = new Intent($this->type, $this->field);
        $intent->strategy = $this->strategy;
        $intent->model = $this->model;
        $intent->filters = $this->filters;
//        $intent = new Intent(
//            strategy: $this->type,
//            model: $this->field,
//            filters: $this->filters,
//        );

        return $intent;
    }
}