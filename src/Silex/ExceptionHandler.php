<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex;

use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Default exception handler.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExceptionHandler implements EventSubscriberInterface
{
    protected $debug;

    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    public function onSilexError(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof FlattenException) {
            $exception = FlattenException::createFromThrowable($exception);
        }

        if ($exception->getStatusCode() === 500) {
            $exception->setStatusText('Whoops, looks like something went wrong.');
        }

        $response = (new Response(sprintf('%d %s', $exception->getStatusCode(), $exception->getStatusText()), $exception->getStatusCode(), $exception->getHeaders()))
            ->setCharset(ini_get('default_charset'));

        $event->setResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onSilexError', -255]];
    }
}
