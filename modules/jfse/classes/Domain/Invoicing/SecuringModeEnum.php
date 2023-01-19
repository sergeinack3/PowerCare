<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static UNSECURED()
 * @method static static CARDLESS()
 * @method static static VISIT()
 * @method static static SECURED()
 * @method static static DESYNCHRONIZED()
 * @method static static DEGRADED()
 */
final class SecuringModeEnum extends JfseEnum
{
    /** @var int */
    private const UNSECURED = 0;

    /** @var int */
    private const CARDLESS = 1;

    /** @var int */
    private const VISIT = 2;

    /** @var int */
    private const SECURED = 3;

    /** @var int */
    private const DESYNCHRONIZED = 4;

    /** @var int */
    private const DEGRADED = 5;
}
