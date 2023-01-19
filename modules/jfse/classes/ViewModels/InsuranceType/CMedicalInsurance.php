<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\InsuranceType;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CInsuranceType
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
class CMedicalInsurance extends CInsuranceType
{
    /** @var string */
    public $code_exoneration_disease;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['code_exoneration_disease'] = 'enum list|0|31|35|4|7|5 notNull';

        return $props;
    }

    public static function getFromEntity(AbstractEntity $entity): CJfseViewModel
    {
        $view_model = parent::getFromEntity($entity);
        /* Mandatory type conversion, or else the mb_field doesn't detect the selected value */
        $view_model->code_exoneration_disease = (string)$view_model->code_exoneration_disease;

        return $view_model;
    }
}
