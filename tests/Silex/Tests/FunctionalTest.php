<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Silex\Tests;

use PHPUnit\Framework\TestCase;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class FunctionalTest extends TestCase
{
    public function testBind()
    {
        $app = new Application();

        $app->get('/', function () {
            return 'hello';
        })
            ->bind('homepage');

        $app->get('/foo', function () {
            return 'foo';
        })
            ->bind('foo_abc');

        $app->flush();
        $routes = $app['routes'];
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('homepage'));
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $routes->get('foo_abc'));
    }

    public function testMount()
    {
        $mounted = new ControllerCollection(new Route());
        $mounted->get('/{name}', function ($name) {
            return new Response($name);
        });

        $app = new Application();
        $app->mount('/hello', $mounted);

        $response = $app->handle(Request::create('/hello/Silex'));
        $this->assertEquals('Silex', $response->getContent());
    }
}
