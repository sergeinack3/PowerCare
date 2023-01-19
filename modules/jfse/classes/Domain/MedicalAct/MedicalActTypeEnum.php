<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static NGAP()
 * @method static static CCAM()
 * @method static static ID()
 * @method static static IK()
 */
class MedicalActTypeEnum extends JfseEnum
{
    private const NGAP = 0;
    private const CCAM = 1;
    private const ID = 2;
    private const IK = 3;
}
