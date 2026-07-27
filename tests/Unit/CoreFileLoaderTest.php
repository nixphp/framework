<?php

namespace Tests\Unit;

use NixPHP\Support\CoreFileLoader;
use Tests\NixPHPTestCase;

class CoreFileLoaderTest extends NixPHPTestCase
{
    private const string LEGACY = BASE_PATH . '/plugins/legacy-layout';
    private const string MODERN = BASE_PATH . '/plugins/modern-layout';
    private const string BARE   = BASE_PATH . '/plugins/bare-layout';

    public function testFileResolvesTheFirstExistingCandidate()
    {
        $this->assertSame(
            self::LEGACY . '/src/config.php',
            CoreFileLoader::file(self::LEGACY, CoreFileLoader::CONFIG_FILES)
        );

        $this->assertSame(
            self::MODERN . '/app/config.php',
            CoreFileLoader::file(self::MODERN, CoreFileLoader::CONFIG_FILES)
        );
    }

    public function testFileReturnsNullWhenNoCandidateExists()
    {
        $this->assertNull(CoreFileLoader::file(self::BARE, CoreFileLoader::CONFIG_FILES));
    }

    public function testFileReturnsNullForAnUnknownRoot()
    {
        $this->assertNull(CoreFileLoader::file(null, CoreFileLoader::CONFIG_FILES));
        $this->assertNull(CoreFileLoader::file('  ', CoreFileLoader::CONFIG_FILES));
    }

    public function testDirectoryAcceptsBothViewLayouts()
    {
        $this->assertSame(
            self::LEGACY . '/src/views',
            CoreFileLoader::directory(self::LEGACY, CoreFileLoader::VIEW_PATHS)
        );

        $this->assertSame(
            self::MODERN . '/views',
            CoreFileLoader::directory(self::MODERN, CoreFileLoader::VIEW_PATHS)
        );
    }

    public function testDirectoryIgnoresFilesAndFileIgnoresDirectories()
    {
        $this->assertNull(CoreFileLoader::directory(self::LEGACY, ['src/config.php']));
        $this->assertNull(CoreFileLoader::file(self::LEGACY, ['src/views']));
    }

    public function testPluginIsBuiltFromTheLegacyLayout()
    {
        $plugin = CoreFileLoader::createPlugin('test/legacy', self::LEGACY);

        $this->assertSame([self::LEGACY . '/src/config.php'], $plugin->getConfigPaths());
        $this->assertSame([self::LEGACY . '/src/routes.php'], $plugin->getRouteFiles());
        $this->assertSame([self::LEGACY . '/src/views'], $plugin->getViewPaths());
        $this->assertSame([self::LEGACY . '/src/functions.php'], $plugin->getFunctionsFiles());
        $this->assertSame([self::LEGACY . '/src/view_helpers.php'], $plugin->getViewHelpersFiles());
    }

    public function testPluginIsBuiltFromTheModernLayout()
    {
        $plugin = CoreFileLoader::createPlugin('test/modern', self::MODERN);

        $this->assertSame([self::MODERN . '/app/config.php'], $plugin->getConfigPaths());
        $this->assertSame([self::MODERN . '/app/routes.php'], $plugin->getRouteFiles());
        $this->assertSame([self::MODERN . '/views'], $plugin->getViewPaths());
    }

    public function testPluginRegistersNoDeadPaths()
    {
        $plugin = CoreFileLoader::createPlugin('test/bare', self::BARE);

        $this->assertSame([], $plugin->getConfigPaths());
        $this->assertSame([], $plugin->getRouteFiles());
        $this->assertSame([], $plugin->getViewPaths());
        $this->assertSame([], $plugin->getFunctionsFiles());
        $this->assertSame([], $plugin->getViewHelpersFiles());
    }

    public function testModernPluginRegistersNoViewHelpersWhenAbsent()
    {
        $plugin = CoreFileLoader::createPlugin('test/modern', self::MODERN);

        $this->assertSame([], $plugin->getViewHelpersFiles());
    }

}
