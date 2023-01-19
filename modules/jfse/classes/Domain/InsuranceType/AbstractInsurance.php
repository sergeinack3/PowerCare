<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\InsuranceType;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Class AbstractInsuranceTypeEntity
 *
 * @package Ox\Mediboard\Jfse\Domain\InsuranceType
 */
abstract class AbstractInsurance extends AbstractEntity
{
    /** @var InsuranceType */
    protected $insurance_type;

    /**
     * @return InsuranceType
     */
    public function getInsuranceType(): InsuranceType
    {
        return $this->insurance_type;
    }
}
