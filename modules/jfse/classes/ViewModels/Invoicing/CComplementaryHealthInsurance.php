<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Invoicing\ComplementaryHealthInsurance;
use Ox\Mediboard\Jfse\ViewModels\Invoicing\CThirdPartyPaymentAssistant;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;
use Ox\Mediboard\Jfse\ViewModels\Convention\CConvention;
use Ox\Mediboard\Jfse\ViewModels\Formula\CFormula;
use Ox\Mediboard\Jfse\ViewModels\VitalCard\CAdditionalHealthInsurance;
use Ox\Mediboard\Jfse\ViewModels\VitalCard\CAmoServicePeriod;
use Ox\Mediboard\Jfse\ViewModels\VitalCard\CCardHealthInsurance;
use Ox\Mediboard\Jfse\ViewModels\VitalCard\CPeriod;
use Ox\Mediboard\SalleOp\CActeCCAM;

class CComplementaryHealthInsurance extends CJfseViewModel
{
    /** @var int */
    public $third_party_amo;

    /** @var int */
    public $third_party_amc;

    /** @var bool - victime attentat */
    public $attack_victim;

    /** @var bool */
    public $third_party_sncf;

    /** @var CAmoServicePeriod */
    public $amo_service;

    /** @var CCardHealthInsurance */
    public $health_insurance;

    /** @var CAdditionalHealthInsurance */
    public $additional_health_insurance;

    /** @var CConvention */
    public $convention;

    /** @var CFormula */
    public $formula;

    /** @var CAcs */
    public $acs;

    /** @var CThirdPartyPaymentAssistant */
    public $assistant;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["third_party_amo"] = "bool";
        $props["third_party_amc"] = "enum list|0|1|2";
        $props["attack_victim"] = "bool";
        $props["third_party_sncf"] = "bool";

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
        /** @var ComplementaryHealthInsurance $entity */
        $view_model = parent::getFromEntity($entity);

        $view_model->third_party_amc = (string)$view_model->third_party_amc;

        if ($entity->getAmoService()) {
            $view_model->amo_service = CAmoServicePeriod::getFromEntity($entity->getAmoService());
        }

        if ($entity->getHealthInsurance()) {
            $view_model->health_insurance = CCardHealthInsurance::getFromEntity($entity->getHealthInsurance());
        } else {
            $view_model->health_insurance = new CCardHealthInsurance();
            $view_model->health_insurance->health_insurance_periods_rights = new CPeriod();
        }

        if ($entity->getAdditionalHealthInsurance()) {
            $view_model->additional_health_insurance =
                CAdditionalHealthInsurance::getFromEntity($entity->getAdditionalHealthInsurance());
        } else {
            $view_model->additional_health_insurance = new CAdditionalHealthInsurance();
        }

        if ($entity->getAcs()) {
            $view_model->acs = CAcs::getFromEntity($entity->getAcs());
        }

        if ($entity->getConvention()) {
            $view_model->convention = CConvention::getFromEntity($entity->getConvention());
        }

        if ($entity->getFormula()) {
            $view_model->formula = CFormula::getFromEntity($entity->getFormula());
        }

        if ($entity->getAssistant()) {
            $view_model->assistant = CThirdPartyPaymentAssistant::getFromEntity($entity->getAssistant());
        }

        return $view_model;
    }
}
