<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\PrescribingPhysician;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static CORRESPONDANT_PHYSICIAN()
 * @method static static OTHER_CARE_PATH_SITUATIONS()
 * @method static static OUT_OF_CARE_PATH()
 * @method static static REFERRING_PHYSICIAN()
 */
class PhysicianOriginEnum extends JfseEnum
{
    private const CORRESPONDANT_PHYSICIAN    = 'O';
    private const OTHER_CARE_PATH_SITUATIONS = 'P';
    private const OUT_OF_CARE_PATH           = 'S';
    private const REFERRING_PHYSICIAN        = 'T';
}
