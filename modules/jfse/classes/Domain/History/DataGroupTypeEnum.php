<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\History;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static SSV()
 * @method static static INPUT_STS()
 * @method static static OUTPUT_STS()
 * @method static static B2_FSE()
 * @method static static B2_DRE()
 */
final class DataGroupTypeEnum extends JfseEnum
{
    /** @var int */
    private const SSV = 0;

    /** @var int */
    private const INPUT_STS = 1;

    /** @var int */
    private const OUTPUT_STS = 2;

    /** @var int */
    private const B2_FSE = 3;

    /** @var int */
    private const B2_DRE = 4;
}
