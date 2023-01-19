<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Interop\Dicom\CDicomSender;
use Ox\Interop\Ftp\CSenderFTP;
use Ox\Interop\Ftp\CSenderSFTP;
use Ox\Interop\Hl7\CSenderMLLP;
use Ox\Interop\Webservices\CSenderSOAP;
use Ox\Mediboard\System\CSenderFileSystem;
use Ox\Mediboard\System\CSenderHTTP;

class CInteropSenderFactory extends CInteropActorFactory
{
    /** @var string */
    protected const MAIN_ACTOR = CInteropSender::class;

    /**
     * @param string|null $actor_type
     *
     * @return CSenderHTTP|CInteropActor
     * @throws CEAIException
     */
    public function makeHTTP(?string $actor_type = null): CSenderHTTP
    {
        return $this->make(CSenderHTTP::class, $actor_type);
    }

    /**
     * @param string|null $actor_type
     *
     * @return CSenderMLLP|CInteropActor
     * @throws CEAIException
     */
    public function makeMLLP(?string $actor_type = null): CSenderMLLP
    {
        return $this->make(CSenderMLLP::class, $actor_type);
    }

    /**
     * @param string|null $actor_type
     *
     * @return CSenderFTP|CInteropActor
     * @throws CEAIException
     */
    public function makeFTP(?string $actor_type = null): CSenderFTP
    {
        return $this->make(CSenderFTP::class, $actor_type);
    }


    /**
     * @param string|null $actor_type
     *
     * @return CSenderSFTP|CInteropActor
     * @throws CEAIException
     */
    public function makeSFTP(?string $actor_type = null): CSenderSFTP
    {
        return $this->make(CSenderSFTP::class, $actor_type);
    }


    /**
     * @param string|null $actor_type
     *
     * @return CSenderSOAP|CInteropActor
     * @throws CEAIException
     */
    public function makeSOAP(?string $actor_type = null): CSenderSOAP
    {
        return $this->make(CSenderSOAP::class, $actor_type);
    }

    /**
     * @param string|null $actor_type
     *
     * @return CDicomSender|CInteropActor
     * @throws CEAIException
     */
    public function makeDicom(?string $actor_type = null): CDicomSender
    {
        return $this->make(CDicomSender::class, $actor_type);
    }


    /**
     * @param string|null $actor_type
     *
     * @return CSenderFileSystem|CInteropActor
     * @throws CEAIException
     */
    public function makeFS(?string $actor_type = null): CSenderFileSystem
    {
        return $this->make(CSenderFileSystem::class, $actor_type);
    }
}
