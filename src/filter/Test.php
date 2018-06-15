<?php

namespace linkphp\router\filter;

use linkphp\router\RouterFilter;

class Test implements RouterFilter
{

    public function handle()
    {
        echo 'filter';
        // TODO: Implement handle() method.
    }

}