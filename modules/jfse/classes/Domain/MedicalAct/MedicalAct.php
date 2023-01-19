<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use DateTime;
use Exception;
use Ox\Mediboard\Jfse\DataModels\CJfseAct;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Formula\Formula;
use Ox\Mediboard\Jfse\Exceptions\MedicalAct\MedicalActException;

/**
 * Represents a MedicalAct (NGAP or CCAM)
 */
class MedicalAct extends AbstractEntity
{
    /** @var MedicalActTypeEnum */
    protected $type;

    /** @var string */
    protected $id;

    /** @var string */
    protected $session_id;

    /** @var string */
    protected $external_id;

    /** @var DateTime */
    protected $date;

    /** @var DateTime */
    protected $completion_date;

    /** @var string */
    protected $act_code;

    /** @var string */
    protected $key_letter;

    /** @var int */
    protected $quantity;

    /** @var float */
    protected $coefficient;

    /** @var string */
    protected $spend_qualifier;

    /** @var Pricing */
    protected $pricing;

    /** @var string */
    protected $execution_place;

    /** @var string */
    protected $additional;

    /** @var string */
    protected $activity_code;

    /** @var string */
    protected $phase_code;

    /** @var string[] */
    protected $modifiers;

    /** @var string */
    protected $association_code;

    /** @var string[] */
    protected $teeth;

    /** @var bool */
    protected $referential_use;

    /** @var string */
    protected $regrouping_code;

    /** @var string */
    protected $exoneration_user_fees;

    /** @var bool */
    protected $unique_exceeding;

    /** @var string */
    protected $exoneration_proof_code;

    /** @var int */
    protected $nurse_reduction_rate;

    /** @var bool */
    protected $locked;

    /** @var string */
    protected $locked_message;

    /** @var bool */
    protected $is_honorary;

    /** @var bool */
    protected $is_lpp;

    /** @var string */
    protected $label;

    /** @var bool */
    protected $authorised_amo_forcing;

    /** @var bool */
    protected $authorised_amc_forcing;

    /** @var bool */
    protected $dental_prosthesis;

    /** @var PriorAgreement */
    protected $prior_agreement;

    /** @var CommonPrevention */
    protected $common_prevention;

    /** @var ExecutingPhysician */
    protected $executing_physician;

    /** @var Formula */
    protected $formula;

    /** @var InsuranceAmountForcing */
    protected $amo_amount_forcing;

    /** @var InsuranceAmountForcing */
    protected $amc_amount_forcing;

    /** @var LppBenefit[] */
    protected $lpp_benefit;

    /** @var CJfseAct */
    protected $data_model;

    /** @var string[] A list on complement codes used in case of free medical care, work accident */
    public static $complement_list = ['ATD', 'DAP', 'DAT', 'DDT', 'DHT', 'DLT', 'DPS', 'SGA', 'SGN'];

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return MedicalActTypeEnum
     */
    public function getType(): ?MedicalActTypeEnum
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    /**
     * @return string
     */
    public function getExternalId(): ?string
    {
        return $this->external_id;
    }

    /**
     * We use the object guid as external, byt Jfse doesn't allow special characters in the external id
     *
     * @return string
     */
    public function getExternalIdInJfseFormat(): ?string
    {
        return str_replace('-', ' ', $this->external_id);
    }

    /**
     * @return DateTime
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @return DateTime
     */
    public function getCompletionDate(): ?DateTime
    {
        return $this->completion_date;
    }

    /**
     * @return string
     */
    public function getActCode(): ?string
    {
        return $this->act_code;
    }

    /**
     * @return string
     */
    public function getKeyLetter(): ?string
    {
        return $this->key_letter;
    }

    /**
     * @return int
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getCoefficient(): ?float
    {
        return $this->coefficient;
    }

    /**
     * @return string
     */
    public function getSpendQualifier(): ?string
    {
        return $this->spend_qualifier;
    }

    /**
     * @return Pricing
     */
    public function getPricing(): ?Pricing
    {
        return $this->pricing;
    }

    /**
     * @return string
     */
    public function getExecutionPlace(): ?string
    {
        return $this->execution_place;
    }

    /**
     * @return string
     */
    public function getAdditional(): ?string
    {
        return $this->additional;
    }

    /**
     * @return string
     */
    public function getActivityCode(): ?string
    {
        return $this->activity_code;
    }

    /**
     * @return string
     */
    public function getPhaseCode(): ?string
    {
        return $this->phase_code;
    }

    /**
     * @return string[]
     */
    public function getModifiers(): ?array
    {
        return $this->modifiers;
    }

    /**
     * @return string
     */
    public function getAssociationCode(): ?string
    {
        return $this->association_code;
    }

    /**
     * @return string[]
     */
    public function getTeeth(): ?array
    {
        return $this->teeth;
    }

    /**
     * @return string
     */
    public function getTeethString(): ?string
    {
        return implode('-', $this->teeth);
    }

    /**
     * @return bool
     */
    public function getReferentialUse(): ?bool
    {
        return $this->referential_use;
    }

    /**
     * @return string
     */
    public function getRegroupingCode(): ?string
    {
        return $this->regrouping_code;
    }

    /**
     * @return string
     */
    public function getExonerationUserFees(): ?string
    {
        return $this->exoneration_user_fees;
    }

    /**
     * @return bool
     */
    public function getUniqueExceeding(): ?bool
    {
        return $this->unique_exceeding;
    }

    /**
     * @return string
     */
    public function getExonerationProofCode(): ?string
    {
        return $this->exoneration_proof_code;
    }

    /**
     * @return bool
     */
    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    /**
     * @return string
     */
    public function getLockedMessage(): ?string
    {
        return $this->locked_message;
    }

    /**
     * @return bool
     */
    public function getIsHonorary(): ?bool
    {
        return $this->is_honorary;
    }

    /**
     * @return bool
     */
    public function getIsLpp(): ?bool
    {
        return $this->is_lpp;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function getAuthorisedAmoForcing(): ?bool
    {
        return $this->authorised_amo_forcing;
    }

    /**
     * @return bool
     */
    public function getAuthorisedAmcForcing(): ?bool
    {
        return $this->authorised_amc_forcing;
    }

    /**
     * @return bool
     */
    public function getDentalProsthesis(): ?bool
    {
        return $this->dental_prosthesis;
    }

    /**
     * @return PriorAgreement
     */
    public function getPriorAgreement(): ?PriorAgreement
    {
        return $this->prior_agreement;
    }

    /**
     * @return CommonPrevention
     */
    public function getCommonPrevention(): ?CommonPrevention
    {
        return $this->common_prevention;
    }

    /**
     * @return ExecutingPhysician
     */
    public function getExecutingPhysician(): ?ExecutingPhysician
    {
        return $this->executing_physician;
    }

    /**
     * @param ExecutingPhysician $physician
     *
     * @return $this
     */
    public function setExecutingPhysician(ExecutingPhysician $physician): self
    {
        $this->executing_physician = $physician;

        return $this;
    }

    /**
     * @return Formula|null
     */
    public function getFormula(): ?Formula
    {
        return $this->formula;
    }

    /**
     * @param Formula $formula
     *
     * @return $this
     */
    public function setFormula(Formula $formula): self
    {
        $this->formula = $formula;

        return $this;
    }

    /**
     * @return InsuranceAmountForcing
     */
    public function getAmoAmountForcing(): ?InsuranceAmountForcing
    {
        return $this->amo_amount_forcing;
    }

    /**
     * @param InsuranceAmountForcing $amo_amount_forcing
     *
     * @return $this
     */
    public function setAmoAmountForcing(InsuranceAmountForcing $amo_amount_forcing): self
    {
        $this->amo_amount_forcing = $amo_amount_forcing;

        return $this;
    }

    /**
     * @return InsuranceAmountForcing
     */
    public function getAmcAmountForcing(): ?InsuranceAmountForcing
    {
        return $this->amc_amount_forcing;
    }

    /**
     * @param InsuranceAmountForcing $amc_amount_forcing
     *
     * @return $this
     */
    public function setAmcAmountForcing(InsuranceAmountForcing $amc_amount_forcing): self
    {
        $this->amc_amount_forcing = $amc_amount_forcing;

        return $this;
    }

    /**
     * @return LppBenefit
     */
    public function getLppBenefit(): LppBenefit
    {
        return $this->lpp_benefit;
    }

    /**
     * @return int
     */
    public function getNurseReductionRate(): ?int
    {
        return $this->nurse_reduction_rate;
    }

    /**
     * Loads the data object linked to the Jfse act's id
     *
     * @return CJfseAct
     *
     * @throws Exception
     * @throws MedicalActException
     */
    public function loadDataModel(): CJfseAct
    {
        if (!$this->data_model || !$this->data_model->_id) {
            $this->data_model          = new CJfseAct();
            $this->data_model->jfse_id = $this->id;

            try {
                $this->data_model->loadMatchingObjectEsc();
            } catch (Exception $e) {
                throw MedicalActException::persistenceError($e->getMessage(), $e);
            }

            if (!$this->external_id) {
                $this->external_id = $this->data_model->_guid;
            }
        }

        return $this->data_model;
    }

    /**
     * Stores the given data object.
     *
     * Throws a UserExceptions in case of error
     *
     * @return bool
     * @throws MedicalActException
     */
    protected function storeDataModel(): bool
    {
        try {
            if ($error = $this->data_model->store()) {
                throw MedicalActException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw MedicalActException::persistenceError($e->getMessage(), $e);
        }

        return true;
    }

    /**
     * Creates the data model object, set the jfse user id and stores it
     *
     * @param string $invoice_id The CJfseInvoice's id
     *
     * @return true
     *
     * @throws Exception
     */
    public function createDataModel(string $invoice_id): bool
    {
        $result   = false;
        $act_guid = explode('-', $this->external_id);
        if (count($act_guid) === 2) {
            $this->data_model = new CJfseAct();
            $this->data_model->setAct($act_guid[0], $act_guid[1]);
            $this->data_model->setInvoiceId($invoice_id);
            $this->data_model->jfse_id = $this->id;

            $result = $this->storeDataModel();
        }

        return $result;
    }

    /**
     * @return bool
     * @throws MedicalActException
     */
    public function deleteDataModel(): bool
    {
        try {
            $this->loadDataModel();
            if ($error = $this->data_model->delete()) {
                throw MedicalActException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw MedicalActException::persistenceError($e->getMessage(), $e);
        }

        return true;
    }
}
