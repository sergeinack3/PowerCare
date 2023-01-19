<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalAct;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;
use Ox\Mediboard\Jfse\ViewModels\Formula\CFormula;

class CJfseActView extends CJfseViewModel
{
    /** @var string */
    public $type;

    /** @var string */
    public $id;

    /** @var string */
    public $session_id;

    /** @var string */
    public $external_id;

    /** @var string */
    public $date;

    /** @var string */
    public $completion_date;

    /** @var string */
    public $act_code;

    /** @var string */
    public $key_letter;

    /** @var int */
    public $quantity;

    /** @var int */
    public $coefficient;

    /** @var string */
    public $spend_qualifier;

    /** @var CJfseActPricing */
    public $pricing;

    /** @var string */
    public $execution_place;

    /** @var string */
    public $additional;

    /** @var string */
    public $activity_code;

    /** @var string */
    public $phase_code;

    /** @var string[] */
    public $modifiers;

    /** @var string */
    public $association_code;

    /** @var string[] */
    public $teeth;

    /** @var bool */
    public $referential_use;

    /** @var string */
    public $regrouping_code;

    /** @var string */
    public $exoneration_user_fees;

    /** @var bool */
    public $unique_exceeding;

    /** @var string */
    public $exoneration_proof_code;

    /** @var int */
    public $nurse_reduction_rate;

    /** @var bool */
    public $locked;

    /** @var string */
    public $locked_message;

    /** @var bool */
    public $is_honorary;

    /** @var bool */
    public $is_lpp;

    /** @var string */
    public $label;

    /** @var bool */
    public $authorised_amo_forcing;

    /** @var bool */
    public $authorised_amc_forcing;

    /** @var bool */
    public $dental_prosthesis;

    /** @var CFormula */
    public $formula;

    /** @var CInsuranceAmountForcing */
    public $amo_amount_forcing;

    /** @var CInsuranceAmountForcing */
    public $amc_amount_forcing;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['type'] = 'enum list|0|1|2|3';
        $props['id'] = 'str';
        $props['session_id'] = 'str';
        $props['external_id'] = 'str';
        $props['date'] = 'str';
        $props['completion_date'] = 'str';
        $props['act_code'] = 'str';
        $props['key_letter'] = 'str';
        $props['quantity'] = 'num';
        $props['coefficient'] = 'num';
        $props['spend_qualifier'] = 'str';
        $props['execution_place'] = 'str';
        $props['additional'] = 'str';
        $props['activity_code'] = 'str';
        $props['phase_code'] = 'str';
        $props['association_code'] = 'str';
        $props['referential_use'] = 'bool';
        $props['regrouping_code'] = 'str';
        $props['exoneration_user_fees'] = 'str';
        $props['unique_exceeding'] = 'num';
        $props['exoneration_proof_code'] = 'str';
        $props['nurse_reduction_rate'] = 'num';
        $props['locked'] = 'bool';
        $props['locked_message'] = 'str';
        $props['is_honorary'] = 'bool';
        $props['is_lpp'] = 'bool';
        $props['label'] = 'str';
        $props['authorised_amo_forcing'] = 'bool';
        $props['authorised_amc_forcing'] = 'bool';
        $props['dental_prosthesis'] = 'bool';

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
        /** @var MedicalAct $entity */
        $view_model = parent::getFromEntity($entity);

        $view_model->pricing = CJfseActPricing::getFromEntity($entity->getPricing());
        if ($entity->getFormula()) {
            $view_model->formula = CFormula::getFromEntity($entity->getFormula());
        }
        $view_model->amo_amount_forcing = CInsuranceAmountForcing::getFromEntity($entity->getAmoAmountForcing());
        $view_model->amc_amount_forcing = CInsuranceAmountForcing::getFromEntity($entity->getAmcAmountForcing());

        return $view_model;
    }
}
