<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Dispatcher;

use Ox\Core\CMbException;
use Ox\Interop\Eai\CEAIDispatcher;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CDADispatcher
{
    protected EventDispatcher $dispatcher;
    protected ?CInteropSender $sender = null;
    protected array $result = [];

    public function __construct(CInteropSender $sender = null)
    {
        $this->dispatcher = $dispatcher = new EventDispatcher();
        $this->sender     = $sender;

        $dispatch_eai = function (ContentCdaEvent $event): void {
            if (!$event->isPropagationStopped()) {
                $result = CEAIDispatcher::dispatch($event->getContent(), $this->sender);
                if ($result) {
                    $this->result[] = $result;
                }
            }
        };

        $dispatcher->addListener(ContentCdaEvent::class, $dispatch_eai);
    }

    /**
     * @param CInteropSender|null $sender
     *
     * @return CDADispatcher
     */
    public function setSender(?CInteropSender $sender): CDADispatcher
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @throws CMbException
     */
    public function dispatchFile(CDocumentItem $document): void
    {
        $content = null;
        if ($document instanceof CFile) {
            $content = $document->getContent() ?: null;
        }

        if (!$content) {
            $content = $document->getBinaryContent(true, false);
        }

        if (!$content) {
            throw new CMbException('CDocumentItem.none');
        }

        $this->dispatch($content);
    }

    /**
     * Dispatch content of cda
     *
     * @param string $content
     *
     * @return void
     */
    public function dispatch(string $content): void
    {
        $content_event = new ContentCdaEvent($content);
        $content_event->setSender($this->sender);

        $this->dispatcher->dispatch($content_event);
    }

    /**
     * Add listener
     *
     * @param string $eventName
     * @param        $listener
     * @param int    $priority
     *
     * @return void
     */
    public function addListener(string $eventName, $listener, int $priority = 0): void
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Add subscriber
     *
     * @param EventSubscriberInterface $subscriber
     *
     * @return void
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }
}
