<?php

namespace Tests\Unit;

use PHPico\Core\Asset;
use Tests\PHPicoTestCase;

class AssetTest extends PhpicoTestCase
{

    public function testAssetInternals()
    {
        $asset = new Asset();
        $asset->add('test.css');
        $asset->add('test.js');
        $this->assertSame(['test.css'], $asset->list('css'));
        $this->assertSame(['test.js'], $asset->list('js'));
    }

    public function testAssetRenderCss()
    {
        $asset = new Asset();
        $asset->add('test.css');
        $this->assertSame('<link rel="stylesheet" href="test.css">', $asset->render('css'));
    }

    public function testAssetRenderJs()
    {
        $asset = new Asset();
        $asset->add('test.js');
        $this->assertSame('<script src="test.js"></script>', $asset->render('js'));
    }

}