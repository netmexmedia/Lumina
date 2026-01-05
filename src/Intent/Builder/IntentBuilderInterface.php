<?php

namespace Netmex\Lumina\Intent\Builder;

// TODO Turn this into a interface
use Netmex\Lumina\Intent\FilterIntentInterface;
use Netmex\Lumina\Intent\Intent;

Interface IntentBuilderInterface
{


    public function strategy(string $strategy): self;

    public function model(string $model): self;

    public function addFilter(FilterIntentInterface $filter): self;

    public function build(): Intent;
}