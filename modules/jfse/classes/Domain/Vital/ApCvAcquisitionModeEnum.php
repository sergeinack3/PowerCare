<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use Ox\Mediboard\Jfse\JfseEnum;

final class ApCvAcquisitionModeEnum extends JfseEnum
{
    /** @var int Acquisition of the ApCv is made by an NFC reader handled by JFSE */
    private const NFC = 1;

    /** @var int Acquisition of the ApCv context is made by a QRCode or NFC reader handled by the client */
    private const QRCODE = 2;
}
