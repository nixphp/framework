<?php

namespace Tests\Unit;

use NixPHP\Support\Collection;
use Tests\NixPHPTestCase;

class CollectionTest extends NixPHPTestCase
{

    public function testCollectionInternals()
    {
        $collection = new Collection();
        $collection->add('foo', 'bar');
        $this->assertTrue($collection->has('foo'));
        $this->assertSame('bar', $collection->get('foo'));
        $this->assertSame(['foo' => 'bar'], $collection->all());
    }

}