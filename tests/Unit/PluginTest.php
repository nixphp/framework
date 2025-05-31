<?php

namespace Tests\Unit;

use NixPHP\Support\Plugin;
use Tests\NixPHPTestCase;

class PluginTest extends NixPHPTestCase
{

    public function testPluginInternals()
    {
        $plugin = new Plugin();
        $plugin->addMeta('test/package', 'viewPaths', 'testViewPaths');
        $plugin->addMeta('test/package', 'configPaths', 'testConfigPaths');
        $plugin->addMeta('test/package', 'bootstraps', 'testBootstraps');

        $this->assertSame([
            'test/package' => [
                'viewPaths' => [0 => 'testViewPaths'],
                'configPaths' => [0 => 'testConfigPaths'],
                'bootstraps' => [0 => 'testBootstraps']
            ]
        ], $plugin->all());
    }

    public function testCustomMeta()
    {
        $plugin = new Plugin();
        $plugin->addMeta('test/package', 'customModule', 'customTest');
        $this->assertSame(['customModule' => [0 => 'customTest']], $plugin->getMeta('test/package'));
    }

}