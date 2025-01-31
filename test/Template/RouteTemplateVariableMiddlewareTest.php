<?php

/**
 * @see       https://github.com/mezzio/mezzio-helpers for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-helpers/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-helpers/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Helper\Template;

use Mezzio\Helper\Template\RouteTemplateVariableMiddleware;
use Mezzio\Helper\Template\TemplateVariableContainer;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteTemplateVariableMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $this->request    = $this->prophesize(ServerRequestInterface::class);
        $this->response   = $this->prophesize(ResponseInterface::class);
        $this->handler    = $this->prophesize(RequestHandlerInterface::class);
        $this->container  = new TemplateVariableContainer();
        $this->middleware = new RouteTemplateVariableMiddleware();
    }

    public function testMiddlewareInjectsVariableContainerWithNullRouteIfNoVariableContainerOrRouteResultPresent()
    {
        $this->request
            ->getAttribute(TemplateVariableContainer::class, Argument::type(TemplateVariableContainer::class))
            ->will(function ($args) {
                return $args[1];
            })
            ->shouldBeCalledTimes(1);

        $this->request
            ->getAttribute(RouteResult::class, null)
            ->willReturn(null)
            ->shouldBeCalledTimes(1);

        $this->request
            ->withAttribute(
                TemplateVariableContainer::class,
                Argument::that(function ($container) {
                    TestCase::assertInstanceOf(TemplateVariableContainer::class, $container);
                    TestCase::assertTrue($container->has('route'));
                    TestCase::assertNull($container->get('route'));
                    return $container;
                })
            )
            ->will([$this->request, 'reveal'])
            ->shouldBeCalledTimes(1);
        $this->request
            ->withAttribute(
                \Zend\Expressive\Helper\Template\TemplateVariableContainer::class,
                Argument::that(function ($container) {
                    TestCase::assertInstanceOf(TemplateVariableContainer::class, $container);
                    TestCase::assertTrue($container->has('route'));
                    TestCase::assertNull($container->get('route'));
                    return $container;
                })
            )
            ->will([$this->request, 'reveal'])
            ->shouldBeCalledTimes(1);

        $this->handler
            ->handle(Argument::that([$this->request, 'reveal']))
            ->will([$this->response, 'reveal']);

        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }

    public function testMiddlewareWillInjectNullValueForRouteIfNoRouteResultInRequest()
    {
        $this->request
            ->getAttribute(TemplateVariableContainer::class, Argument::type(TemplateVariableContainer::class))
            ->willReturn($this->container)
            ->shouldBeCalledTimes(1);

        $this->request
            ->getAttribute(RouteResult::class, null)
            ->willReturn(null)
            ->shouldBeCalledTimes(1);

        $originalContainer = $this->container;
        $this->request
            ->withAttribute(
                TemplateVariableContainer::class,
                Argument::that(function ($container) use ($originalContainer) {
                    TestCase::assertNotSame($container, $originalContainer);
                    TestCase::assertTrue($container->has('route'));
                    TestCase::assertNull($container->get('route'));
                    return $container;
                })
            )
            ->will([$this->request, 'reveal'])
            ->shouldBeCalledTimes(1);
        $this->request
            ->withAttribute(
                \Zend\Expressive\Helper\Template\TemplateVariableContainer::class,
                Argument::that(function ($container) use ($originalContainer) {
                    TestCase::assertNotSame($container, $originalContainer);
                    TestCase::assertTrue($container->has('route'));
                    TestCase::assertNull($container->get('route'));
                    return $container;
                })
            )
            ->will([$this->request, 'reveal'])
            ->shouldBeCalledTimes(1);

        $this->handler
            ->handle(Argument::that([$this->request, 'reveal']))
            ->will([$this->response, 'reveal']);

        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }

    public function testMiddlewareWillInjectRoutePulledFromRequestRouteResult()
    {
        $routeResult = $this->prophesize(RouteResult::class);

        $this->request
            ->getAttribute(TemplateVariableContainer::class, Argument::type(TemplateVariableContainer::class))
            ->willReturn($this->container)
            ->shouldBeCalledTimes(1);

        $this->request
            ->getAttribute(RouteResult::class, null)
            ->will([$routeResult, 'reveal'])
            ->shouldBeCalledTimes(1);

        $originalContainer = $this->container;
        $this->request
            ->withAttribute(
                TemplateVariableContainer::class,
                Argument::that(function ($container) use ($originalContainer, $routeResult) {
                    TestCase::assertNotSame($container, $originalContainer);
                    TestCase::assertTrue($container->has('route'));
                    TestCase::assertSame($container->get('route'), $routeResult->reveal());
                    return $container;
                })
            )
            ->will([$this->request, 'reveal'])
            ->shouldBeCalledTimes(1);
        $this->request
            ->withAttribute(
                \Zend\Expressive\Helper\Template\TemplateVariableContainer::class,
                Argument::that(function ($container) use ($originalContainer, $routeResult) {
                    TestCase::assertNotSame($container, $originalContainer);
                    TestCase::assertTrue($container->has('route'));
                    TestCase::assertSame($container->get('route'), $routeResult->reveal());
                    return $container;
                })
            )
            ->will([$this->request, 'reveal'])
            ->shouldBeCalledTimes(1);

        $this->handler
            ->handle(Argument::that([$this->request, 'reveal']))
            ->will([$this->response, 'reveal']);

        $this->assertSame(
            $this->response->reveal(),
            $this->middleware->process($this->request->reveal(), $this->handler->reveal())
        );
    }
}
