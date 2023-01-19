<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\ViewModels\VitalCard\CAdditionalHealthInsurance;
use Ox\Mediboard\Jfse\ViewModels\VitalCard\CCardHealthInsurance;

class CPatientVitalCard extends CJfseViewModel
{
    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    /** @var string */
    public $birth_name;

    /** @var string */
    public $birth_date;

    /** @var string */
    public $birth_rank;

    /** @var string */
    public $address;

    /** @var string */
    public $zip_code;

    /** @var string */
    public $city;

    /** @var string */
    public $quality;

    /** @var string */
    public $quality_label;

    /** @var CHealthInsurance */
    public $health_insurance;

    /** @var CAdditionalHealthInsurance */
    public $additional_health_insurance;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["first_name"]    = "str";
        $props["last_name"]     = "str";
        $props['birth_name']    = 'str';
        $props["birth_date"]    = "date";
        $props["birth_rank"]    = "num";
        $props['address']       = 'str';
        $props['zip_code']      = 'str';
        $props['city']          = 'str';
        $props["quality"]       = "str";
        $props["quality_label"] = "str";

        return $props;
    }

    public function __toString(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        $view_model = parent::getFromEntity($entity);

        if ($entity instanceof Beneficiary) {
            $view_model                = parent::getFromEntity($entity->getPatient());
            $view_model->quality       = $entity->getQuality();
            $view_model->quality_label = $entity->getQualityLabel();

            if ($entity->getHealthInsurance()) {
                $view_model->health_insurance = CCardHealthInsurance::getFromEntity($entity->getHealthInsurance());
            }

            if ($entity->getAdditionalHealthInsurance()) {
                $view_model->additional_health_insurance = CAdditionalHealthInsurance::getFromEntity(
                    $entity->getAdditionalHealthInsurance()
                );
            }
        }

        return $view_model;
    }
}
