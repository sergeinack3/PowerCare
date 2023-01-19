<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static BUY()
 * @method static static MAINTENANCE()
 * @method static static RENT()
 * @method static static SHIPPING_FEE()
 * @method static static REPARATION()
 * @method static static SERVICE()
 * @method static static SHIPPING()
 */
class LppTypeEnum extends JfseEnum
{
    private const BUY = 'A';
    private const MAINTENANCE = 'E';
    private const RENT = 'L';
    private const SHIPPING_FEE = 'P';
    private const REPARATION = 'R';
    private const SERVICE = 'S';
    private const SHIPPING = 'V';
}
