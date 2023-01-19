<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\CarePath;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static NOT_CONCERNED()
 * @method static static VALID_CARE_PATH()
 * @method static static OUTSIDE_CARE_PATH()
 */
final class CarePathStatusEnum extends JfseEnum
{
    /** @var int  */
    private const NOT_CONCERNED = 0;
    /** @var int  */
    private const VALID_CARE_PATH = 1;
    /** @var int  */
    private const OUTSIDE_CARE_PATH = 2;
}
