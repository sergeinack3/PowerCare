<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\VitalCard;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Vital\HealthInsurance;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CCardHealthInsurance extends CJfseViewModel
{
    /** @var int */
    public $group;

    /** @var string */
    public $id;

    /** @var string */
    public $effective_guarantees;

    /** @var string */
    public $treatment_indicator;

    /** @var string */
    public $associated_services_type;

    /** @var string */
    public $associated_services;

    /** @var CPeriod[] */
    public $health_insurance_periods_rights;

    /** @var int */
    public $contract_type;

    /** @var string */
    public $pec;

    /** @var bool */
    public $paper_mode;

    /** @var bool */
    public $rights_forcing;

    /** @var bool */
    public $adri_origin;

    /** @var int */
    public $type;

    /** @var string */
    public $associated_services_contract;

    /** @var string */
    public $referral_sts_code;

    /** @var string */
    public $label;

    /** @var bool */
    public $is_c2s;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["group"]                        = "num";
        $props["id"]                           = "num maxLength|10 notNull";
        $props["effective_guarantees"]         = "str";
        $props["treatment_indicator"]          = "str";
        $props["associated_services_type"]     = "str";
        $props["associated_services"]          = "str";
        $props["contract_type"]                = "num";
        $props["pec"]                          = "str";
        $props["paper_mode"]                   = "bool";
        $props["rights_forcing"]               = "bool";
        $props["adri_origin"]                  = "bool";
        $props["type"]                         = "num";
        $props["associated_services_contract"] = "str";
        $props["referral_sts_code"]            = "str";
        $props["label"]                        = "str";
        $props['is_c2s']                       = 'bool';

        return $props;
    }

    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        /** @var HealthInsurance $entity */
        $view_model = parent::getFromEntity($entity);
        $view_model->health_insurance_periods_rights = new CPeriod();
        if ($entity->getHealthInsurancePeriodsRights()) {
            $view_model->health_insurance_periods_rights =
                CPeriod::getFromEntity($entity->getHealthInsurancePeriodsRights());
        }

        return $view_model;
    }
}
