<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static INAPPLICABLE()
 * @method static static URGENT_CONTRACEPTIVE_DELIVERY()
 * @method static static CONTRACEPTIVE_DELIVERY()
 * @method static static CONTRACEPTION_CONSULTATION()
 */
final class AnonymizationEnum extends JfseEnum
{
    /** @var int */
    private const INAPPLICABLE = 0;

    /** @var int */
    private const URGENT_CONTRACEPTIVE_DELIVERY = 1;

    /** @var int */
    private const CONTRACEPTIVE_DELIVERY = 2;

    /** @var int */
    private const CONTRACEPTION_CONSULTATION = 3;

    public static function getProp(): string
    {
        return 'enum list|0|1|2|3 default|0';
    }
}
