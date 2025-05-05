<?php

namespace Fixtures\Events;

class TestEventListener
{

    public function handle()
    {
        return 'test response from class';
    }

}