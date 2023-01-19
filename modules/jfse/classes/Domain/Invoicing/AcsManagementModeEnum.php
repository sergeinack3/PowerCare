<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use MyCLabs\Enum\Enum;
use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static NO_THIRD_PARTY_AMC()
 * @method static static COORDINATED_THIRD_PARTY()
 * @method static static UNIQUE_MANAGEMENT()
 * @method static static SEPARATED_MANAGEMENT()
 */
final class AcsManagementModeEnum extends JfseEnum
{
    /** @var int */
    private const NO_THIRD_PARTY_AMC = 0;

    /** @var int */
    private const COORDINATED_THIRD_PARTY = 1;

    /** @var int */
    private const UNIQUE_MANAGEMENT = 2;

    /** @var int */
    private const SEPARATED_MANAGEMENT = 3;
}
