<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\InsuranceType;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CInsuranceType
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
class CWorkAccidentInsurance extends CInsuranceType
{
    /** @var string */
    public $date;
    /** @var bool */
    public $has_physical_document;
    /** @var int */
    public $number;
    /** @var int */
    public $organisation_support;
    /** @var bool */
    public $is_organisation_identical_amo;
    /** @var string */
    public $organisation_vital;
    /** @var bool */
    public $shipowner_support;
    /** @var int */
    public $amount_apias;

    public static function getFromEntity(AbstractEntity $entity): CJfseViewModel
    {
        $model = parent::getFromEntity($entity);

        if ($entity instanceof WorkAccidentInsurance) {
            $model->date = $entity->getDate() ? $entity->getDate()->format('Y-m-d') : null;
        }

        return $model;
    }

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['date']                          = 'date notNull';
        $props['has_physical_document']         = 'bool notNull';
        $props['number']                        = 'num';
        $props['organisation_support']          = 'num';
        $props['is_organisation_identical_amo'] = 'bool';
        $props['organisation_vital']            = 'enum list|-1|1|2|3';
        $props['shipowner_support']             = 'bool';
        $props['amount_apias']                  = 'currency';

        return $props;
    }
}
