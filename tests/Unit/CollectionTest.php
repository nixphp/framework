<?php

namespace Tests\Unit;

use PHPico\Support\Collection;
use Tests\PHPicoTestCase;

class CollectionTest extends PHPicoTestCase
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