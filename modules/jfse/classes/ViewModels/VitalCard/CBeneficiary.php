<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\VitalCard;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;
use Ox\Mediboard\Jfse\ViewModels\CPatientVitalCard;

class CBeneficiary extends CJfseViewModel
{
    /** @var int */
    public $id;

    /** @var int */
    public $group;

    /** @var int */
    public $number;

    /** @var CPatientVitalCard */
    public $patient;

    /** @var string */
    public $nir;

    /** @var string */
    public $certified_nir;

    /** @var string */
    public $certified_nir_key;

    /** @var string */
    public $nir_certification_date;

    /** @var string */
    public $quality;

    /** @var string */
    public $quality_label;

    /** @var CAmoServicePeriod */
    public $amo_service;

    /** @var string */
    public $insc_number;

    /** @var string */
    public $insc_key;

    /** @var string */
    public $insc_error;

    /** @var string|null */
    public $acs;

    /** @var string */
    public $acs_label;

    /** @var CPeriod */
    public $amo_period_rights;

    /** @var CCoverageCodePeriod */
    public $coverage_code_periods;

    /** @var CCardHealthInsurance */
    public $health_insurance;

    /** @var CAdditionalHealthInsurance */
    public $additional_health_insurance;

    /** @var string */
    public $integrator_id;

    // Next, specific to Adri
    /** @var string */
    public $prescribing_physician_top;

    /** @var CDeclaredWorkAccident[] */
    public $declared_work_accidents;

    /** @var bool */
    public $apcv;

    /** @var CApCvContext */
    public $apcv_context;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["id"]                        = "str";
        $props["group"]                     = "num";
        $props["number"]                    = "num";
        $props["nir"]                       = 'str';
        $props["certified_nir"]             = "str";
        $props['certified_nir_key']         = 'str';
        $props['nir_certification_date']    = 'date';
        $props["quality"]                   = "str";
        $props["quality_label"]             = "str";
        $props['insc_number']               = 'str';
        $props['insc_key']                  = 'str';
        $props['insc_error']                = 'str';
        $props['acs']                       = 'str';
        $props['acs_label']                 = 'str';
        $props['prescribing_physician_top'] = 'str';
        $props['apcv']                      = 'bool';

        return $props;
    }

    /**
     * Create a new view model and sets its properties from the given entity
     *
     * @param AbstractEntity $entity
     *
     * @return static|null
     */
    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        /** @var Beneficiary $entity */
        $view_model = parent::getFromEntity($entity);

        $view_model->patient = CPatientVitalCard::getFromEntity($entity->getPatient());

        if ($entity->getAmoService()) {
            $view_model->amo_service = CAmoServicePeriod::getFromEntity($entity->getAmoService());
        }

        if ($entity->getLastAmoRight()) {
            $view_model->amo_period_rights = CPeriod::getFromEntity($entity->getLastAmoRight());
        }

        if ($entity->getCurrentCoverage()) {
            $view_model->coverage_code_periods = CCoverageCodePeriod::getFromEntity($entity->getCurrentCoverage());
        }

        if ($entity->getHealthInsurance()) {
            $view_model->health_insurance = CCardHealthInsurance::getFromEntity($entity->getHealthInsurance());
        }

        if ($entity->getAdditionalHealthInsurance()) {
            $view_model->additional_health_insurance =
                CAdditionalHealthInsurance::getFromEntity($entity->getAdditionalHealthInsurance());
        }

        if ($entity->getDeclaredWorkAccidents()) {
            $view_model->declared_work_accidents = [];
            foreach ($entity->getDeclaredWorkAccidents() as $index => $accident) {
                $view_model->declared_work_accidents[$index] = CDeclaredWorkAccident::getFromEntity($accident);
            }
        }

        if ($entity->getApcvContext()) {
            $view_model->apcv_context = CApCvContext::getFromEntity($entity->getApcvContext());
        }

        return $view_model;
    }
}
