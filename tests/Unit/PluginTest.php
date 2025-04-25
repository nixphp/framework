<?php

namespace Tests\Unit;

use PHPico\Support\Plugin;
use Tests\PHPicoTestCase;

class PluginTest extends PHPicoTestCase
{

    public function testPluginInternals()
    {
        $plugin = new Plugin();
        $plugin->addMeta('viewPaths', 'testViewPaths');
        $plugin->addMeta('configPaths', 'testConfigPaths');
        $plugin->addMeta('bootstraps', 'testBootstraps');

        $this->assertSame([
            'viewPaths' => [0 => 'testViewPaths'],
            'configPaths' => [0 => 'testConfigPaths'],
            'bootstraps' => [0 => 'testBootstraps']
        ], $plugin->all());
    }

    public function testCustomMeta()
    {
        $plugin = new Plugin();
        $plugin->addMeta('customModule', 'customTest');
        $this->assertSame([0 => 'customTest'], $plugin->getMeta('customModule'));
    }

}