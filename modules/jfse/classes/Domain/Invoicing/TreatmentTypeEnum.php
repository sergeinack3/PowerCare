<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static UNDEFINED()
 * @method static static ISOLATED_ACTS()
 * @method static static ACTS_SERIE()
 */
final class TreatmentTypeEnum extends JfseEnum
{
    /** @var int */
    private const UNDEFINED = 0;

    /** @var int */
    private const ISOLATED_ACTS = 1;

    /** @var int */
    private const ACTS_SERIE = 2;
}
