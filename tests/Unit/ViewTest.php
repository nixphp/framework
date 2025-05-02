<?php

namespace Tests\Unit;

use NixPHP\Core\View;
use Tests\NixPHPTestCase;

class ViewTest extends NixPHPTestCase
{

    public function testViewCreation()
    {
        $view = new View();
        $view->setTemplate('test');
        $this->assertSame('content', $view->render());
    }

    public function testTemplateNotFoundException()
    {
        $this->expectException(\RuntimeException::class);
        $view = new View();
        $view->setTemplate('test_not_exists');
    }

    public function testViewCreationWithVariables()
    {
        $view = new View();
        $view->setTemplate('test_var');
        $view->setVariable('foo', 'bar');
        $this->assertSame('foo,bar', $view->render());
    }

    public function testViewCreationWithLayout()
    {
        $view = new View();
        $view->setTemplate('test_layout');
        $this->assertSame('layout,content', trim($view->render()));
    }

    public function testMissingOpenedBlockInView()
    {
        $this->expectException(\Exception::class);
        $view = new View();
        $view->setTemplate('test_missing_block');
        $view->render();
    }

    public function testMaliciousTemplatePath()
    {
        $this->expectException(\InvalidArgumentException::class);
        $view = new View();
        $view->setTemplate('../../../../etc/passwd');
        $view->render();
    }

    public function testHelperFunction()
    {
        $this->assertIsString(\NixPHP\view('test'));
    }

}