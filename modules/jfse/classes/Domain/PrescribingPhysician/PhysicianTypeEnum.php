<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\PrescribingPhysician;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static LIBERAL()
 * @method static static EMPLOYED()
 * @method static static VOLUNTEER()
 */
class PhysicianTypeEnum extends JfseEnum
{
    private const LIBERAL   = 0;
    private const EMPLOYED  = 1;
    private const VOLUNTEER = 2;
}
