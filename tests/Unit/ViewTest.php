<?php

namespace Tests\Unit;

use PHPico\Core\View;
use Tests\PHPicoTestCase;

class ViewTest extends PHPicoTestCase
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
        $this->assertIsString(\PHPico\view('test'));
    }

}