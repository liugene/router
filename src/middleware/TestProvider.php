<?php

namespace linkphp\router\middleware;

use linkphp\event\EventDefinition;
use linkphp\event\EventServerProvider;

class TestProvider implements  EventServerProvider
{

    public function update(EventDefinition $definition){}

}