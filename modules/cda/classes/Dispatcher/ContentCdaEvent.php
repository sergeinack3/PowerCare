<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Dispatcher;

use Exception;
use Ox\Core\CApp;
use Ox\Interop\Cda\CCDADomDocument;
use Ox\Interop\Eai\CInteropSender;
use Symfony\Contracts\EventDispatcher\Event;

class ContentCdaEvent extends Event
{
    private string           $content;
    private ?CCDADomDocument $document             = null;
    private ?string          $document_code        = null;
    private ?string          $document_system_code = null;
    private ?CInteropSender  $sender               = null;

    public function __construct(string $content)
    {
        try {
            $this->content  = $content;
            $this->document = $document = new CCDADomDocument();
            $document->loadXML($content);
            $document->getContentNodes();

            $this->document_code        = $document->getDocumentCode();
            $this->document_system_code = $document->getDocumentSystemCode();
        } catch (Exception $exception) {
            CApp::log('Error in dispatch CDA', $exception);
        }
    }

    /**
     * @return CCDADomDocument|null
     */
    public function getDocument(): ?CCDADomDocument
    {
        return $this->document;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string|null
     */
    public function getDocumentCode(): ?string
    {
        return $this->document_code;
    }

    /**
     * @return string|null
     */
    public function getDocumentSystemCode(): ?string
    {
        return $this->document_system_code;
    }

    /**
     * @param CInteropSender|null $sender
     *
     * @return ContentCdaEvent
     */
    public function setSender(?CInteropSender $sender): ContentCdaEvent
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return CInteropSender|null
     */
    public function getSender(): ?CInteropSender
    {
        return $this->sender;
    }
}
