<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\InsuranceType;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CInsuranceType
 *
 * @package Ox\Mediboard\Jfse\ViewModels
 */
class CMaternityInsurance extends CInsuranceType
{
    /** @var string */
    public $date;
    /** @var bool */
    public $force_exoneration;

    /**
     * @param MaternityInsurance $insurance
     *
     * @return CJfseViewModel
     */
    public static function getFromEntity(AbstractEntity $insurance): CJfseViewModel
    {
        if (!$insurance instanceof MaternityInsurance) {
            return parent::getFromEntity($insurance);
        }

        $model                    = new static();
        $model->date              = $insurance->getDate() ? $insurance->getDate()->format('Y-m-d') : null;
        $model->force_exoneration = $insurance->getForceExoneration();

        return $model;
    }

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['date']              = 'date notNull';
        $props['force_exoneration'] = 'bool';

        return $props;
    }
}
