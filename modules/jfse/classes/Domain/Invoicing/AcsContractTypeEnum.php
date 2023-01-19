<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static THIRD_PARTY_AMO()
 * @method static static ACS_A_CONTRACT()
 * @method static static ACS_B_CONTRACT()
 * @method static static ACS_C_CONTRACT()
 */
final class AcsContractTypeEnum extends JfseEnum
{
    /** @var string */
    private const THIRD_PARTY_AMO = "AMO";

    /** @var string */
    private const ACS_A_CONTRACT = "A";

    /** @var string */
    private const ACS_B_CONTRACT = "B";

    /** @var string */
    private const ACS_C_CONTRACT = "C";
}
