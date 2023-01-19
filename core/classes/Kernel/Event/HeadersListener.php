<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Event;

use Ox\Core\CApp;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HeadersListener implements EventSubscriberInterface
{
    public const EXPIRATION_DATETIME = 'Mon, 26 Jul 1997 05:00:00 GMT';
    public const CACHE_CONTROL       = 'no-cache, no-store, must-revalidate';
    public const PRAGMA              = 'no-cache';
    public const COMPATIBILITY       = 'IE=edge';

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', 90],
        ];
    }

    /**
     * Set custom headers
     *
     * @param ResponseEvent $event
     *
     * @return void
     */
    public function onResponse(ResponseEvent $event)
    {
        $response_headers = $event->getResponse()->headers;

        foreach ($this->getHeaders() as $header_name => $header_value) {
            $response_headers->set($header_name, $header_value);
        }
    }

    /**
     * @return array
     */
    private function getHeaders(): array
    {
        return [
            'Expires'         => self::EXPIRATION_DATETIME, // Date in the past
            'Last-Modified'   => gmdate('D, d M Y H:i:s') . ' GMT',  // always modified
            'Cache-Control'   => self::CACHE_CONTROL, // HTTP/1.1
            'Pragma'          => self::PRAGMA,  // HTTP/1.0
            'X-UA-Compatible' => self::COMPATIBILITY,  // Force IE document mode
            'X-Request-ID'    => CApp::getRequestUID(),  // Correlates HTTP requests between a client and server
        ];
    }
}
