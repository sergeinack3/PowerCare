<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * An enum listing the different types of acknowledgements
 *
 * @method static static POSITIVE()
 * @method static static NEGATIVE()
 */
final class AcknowledgementTypeEnum extends JfseEnum
{
    protected const POSITIVE = 'positive';
    protected const NEGATIVE = 'negative';
}
