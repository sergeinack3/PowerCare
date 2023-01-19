<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Printing;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static MODE_ONE_PRINT()
 * @method static static MODE_MULTIPLE_PRINT()
 * @method static static MODE_PRINT_DATE_BOUNDS()
 * @method static static MODE_ONE_OR_SEVERAL_FILES()
 */
class PrintSlipModeEnum extends JfseEnum
{
    private const MODE_ONE_PRINT            = 1;
    private const MODE_MULTIPLE_PRINT       = 2;
    private const MODE_PRINT_DATE_BOUNDS    = 3;
    private const MODE_ONE_OR_SEVERAL_FILES = 4;
}
