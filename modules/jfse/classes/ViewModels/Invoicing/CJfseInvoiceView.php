<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Jfse\DataModels\CJfseAct;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathDoctor;
use Ox\Mediboard\Jfse\Domain\Invoicing\AnonymizationEnum;
use Ox\Mediboard\Jfse\Domain\Invoicing\Invoice;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoiceStatusEnum;
use Ox\Mediboard\Jfse\Domain\Invoicing\SecuringModeEnum;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\ViewModels\CarePath\CCarePath;
use Ox\Mediboard\Jfse\ViewModels\CarePath\CCarePathDoctor;
use Ox\Mediboard\Jfse\ViewModels\CCommonLawAccident;
use Ox\Mediboard\Jfse\ViewModels\CJfseMessage;
use Ox\Mediboard\Jfse\ViewModels\CJfseQuestion;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;
use Ox\Mediboard\Jfse\ViewModels\CProofAmo;
use Ox\Mediboard\Jfse\ViewModels\InsuranceType\CInsurance;
use Ox\Mediboard\Jfse\ViewModels\PrescribingPhysician\CJfsePrescription;
use Ox\Mediboard\Jfse\ViewModels\UserManagement\CJfseUserView;
use Ox\Mediboard\Jfse\ViewModels\VitalCard\CBeneficiary;
use Ox\Mediboard\Jfse\ViewModels\VitalCard\CInsured;
use Ox\Mediboard\Lpp\CActeLPP;
use Ox\Mediboard\SalleOp\CActeCCAM;

class CJfseInvoiceView extends CJfseViewModel
{
    /** @var string */
    public $id;

    /** @var int */
    public $securing;

    /** @var bool */
    public $alsace_moselle;

    /** @var string */
    public $creation_date;

    /** @var int */
    public $integrator_id;

    /** @var bool */
    public $delay_transmission;

    /** @var int */
    public $anonymize;

    /** @var bool */
    public $paper_mode;

    /** @var int */
    public $fsp_mode;

    /** @var float */
    public $total_amount;

    /** @var float */
    public $total_insured;

    /** @var float */
    public $total_amo;

    /** @var float */
    public $total_amc;

    /** @var int */
    public $invoice_number;

    /** @var float */
    public $amount_owed_amo;

    /** @var float */
    public $amount_owed_amc;

    /** @var int */
    public $treatment_type;

    /** @var bool */
    public $forcing_amo;

    /** @var bool */
    public $forcing_amc;

    /** @var float */
    public $c2s_maximum_amount;

    /** @var string */
    public $amo_right_status;

    /** @var bool */
    public $check_vital_card;

    /** @var float Only use in FSP mode */
    public $global_rate;

    /** @var int */
    public $correct_or_recycle;

    /** @var bool Only used in the initialization of the invoice */
    public $beneficiary_banner_display;

    /** @var bool Only used in the initialization of the invoice */
    public $automatic_deletion;

    /** @var bool Only used in the initialization of the invoice */
    public $sts_disabled;

    /** @var int Only used in the initialization of the invoice */
    public $template_id;

    /** @var CRuleForcing */
    public $rule_forcing;

    /** @var CCarePath */
    public $care_path;

    /** @var CCommonLawAccident */
    public $common_law_accident;

    /** @var CInsurance */
    public $insurance;

    /** @var CProofAmo */
    public $proof_amo;

    /** @var CJfsePrescription */
    public $prescription;

    /** @var CComplementaryHealthInsurance */
    public $complementary_health_insurance;

    /** @var CInsuredParticipationAct[] */
    public $insured_participation_acts;

    /** @var bool */
    public $adri;

    /** @var CBeneficiary */
    public $beneficiary;

    /** @var CInsured */
    public $insured;

    /** @var CJfseUserView */
    public $practitioner;

    /** @var CJfseMessage[] */
    public $messages;

    /** @var CJfseQuestion[] */
    public $questions;

    /** @var CJfseInvoiceUserInterface */
    public $user_interface;

    /** @var CJfseInvoice */
    public $data_model;

    /** @var CJfseUser */
    public $user_data_model;

    /** @var CJfsePatient */
    public $patient_data_model;

    /** @var CConsultation */
    public $consultation;

    /** @var CJfseActView[] */
    public $medical_acts;

    /** @var CJfseAct[] */
    public $linked_acts;

    /** @var CActe[] */
    public $other_invoices_acts;

    /** @var CActe[] */
    public $unlinked_acts;

    /** @var float */
    public $_total_base;

    /** @var float */
    public $_total_exceeding_fees;

    /** @var float */
    public $_total;

    /** @var string */
    public $_status;

    /** @var string */
    public $_status_icon;

    /** @var string */
    public $_status_color;

    /** @var CCarePathDoctor */
    public $_patient_referring_physician;
    /** @var CCarePathDoctor[] */
    public $_patient_corresponding_physicians;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props['id']                    = 'str';
        $props['securing']              = SecuringModeEnum::getProp();
        $props['alsace_moselle']        = 'bool';
        $props['creation_date']         = 'str';
        $props['integrator_id']         = 'num';
        $props['delay_transmission']    = 'bool';
        $props['anonymize']             = AnonymizationEnum::getProp();
        $props['paper_mode']            = 'bool';
        $props['fsp_mode']              = 'num';
        $props['total_amount']          = 'currency';
        $props['total_insured']         = 'currency';
        $props['total_amo']             = 'currency';
        $props['total_amc']             = 'currency';
        $props['invoice_number']        = 'num';
        $props['amount_owed_amo']       = 'currency';
        $props['amount_owed_amc']       = 'currency';
        $props['treatment_type']        = 'num';
        $props['forcing_amo']           = 'bool';
        $props['forcing_amc']           = 'bool';
        $props['c2s_maximum_amount']    = 'currency';
        $props['amo_right_status']      = 'str';
        $props['check_vital_card']      = 'bool';
        $props['global_rate']           = 'float';
        $props['correct_or_recycle']    = 'num';
        $props['invoice_complements']   = 'bool';
        $props['adri']   = 'bool';
        $props['_total_base']           = 'currency';
        $props['_total_exceeding_fees'] = 'currency';
        $props['_total']                = 'currency';
        $props['_status']               = InvoiceStatusEnum::getProp();

        return $props;
    }

    /**
     * Load the acts of the consultation, and check if they are linked to the invoice, unlinked,
     * or linked to another invoice
     *
     * @throws \Exception
     */
    public function loadActs(): void
    {
        if ($this->consultation && $this->consultation->_id && $this->data_model && $this->data_model->_id) {
            $this->linked_acts = [];
            $this->unlinked_acts = [];
            $this->other_invoices_acts = [];

            $this->_total = 0;
            $this->_total_base = 0;
            $this->_total_exceeding_fees = 0;

            foreach ($this->consultation->loadRefsActes() as $act) {
                /** @var CJfseAct $act_data_model */
                $act_data_model = $act->loadUniqueBackRef('jfse_act_link');

                if ($act instanceof CActeCCAM) {
                    $act->getTarif();
                }

                if ($act_data_model && $act_data_model->jfse_invoice_id == $this->data_model->_id) {
                    foreach ($this->medical_acts as $medical_act) {
                        if ($medical_act->external_id == $act->_guid) {
                            $act_data_model->_medical_act = $medical_act;
                        }
                    }

                    $act_data_model->_act = $act;

                    $this->linked_acts[] = $act_data_model;
                    $this->addActPrice($act);
                } elseif (
                    $act_data_model && $act_data_model->_id
                    && $act_data_model->jfse_invoice_id != $this->data_model->_id
                ) {
                    $this->other_invoices_acts[] = $act;
                } else {
                    $this->unlinked_acts[] = $act;
                }
            }
        }
    }

    private function addActPrice(CActe $act): void
    {
        switch ($act->_class) {
            case 'CActeCCAM':
                /** @var CActeCCAM $act */
                $this->_total_base += $act->_tarif;
                $this->_total_exceeding_fees += $act->montant_depassement;
                $this->_total += $act->_total;
                break;
            case 'CActeLPP':
                /** @var CActeLPP $act */
                $act->updateFormFields();
                $this->_total_base += $act->_code_lpp->_last_pricing->price;
                $this->_total_exceeding_fees += ($act->montant_final - $act->_code_lpp->_last_pricing->price);
                $this->_total += $act->montant_final;
                break;
            default:
                /** @var CActeNGAP $act */
                $this->_total_base += $act->montant_base;
                $this->_total_exceeding_fees += $act->montant_depassement;
                $this->_total += $act->_tarif;
        }
    }

    /**
     * Will instantiate new CarePathDoctors objects from the referring physician
     * and the corresponding physicians of the linked CPatient
     *
     * @return self
     */
    public function setCarePathDoctors(): self
    {
        if ($this->patient_data_model) {
            $patient = $this->patient_data_model->loadPatient();
        } elseif ($this->consultation instanceof CConsultation) {
            $patient = $this->consultation->loadRefPatient();
        }

        if ($patient) {
            $patient->loadRefsCorrespondants();

            if ($patient->_ref_medecin_traitant) {
                $this->_patient_referring_physician = new CCarePathDoctor();
                $this->_patient_referring_physician->last_name = $patient->_ref_medecin_traitant->nom;
                $this->_patient_referring_physician->first_name = $patient->_ref_medecin_traitant->prenom;
            }

            if (is_array($patient->_ref_medecins_correspondants) && count($patient->_ref_medecins_correspondants)) {
                $this->_patient_corresponding_physicians = [];
                foreach ($patient->_ref_medecins_correspondants as $_correspondant) {
                    $this->_patient_corresponding_physicians[] = CCarePathDoctor::getFromEntity(
                        (new CarePathDoctor())
                            ->setLastName($_correspondant->_ref_medecin->nom)
                            ->setFirstName($_correspondant->_ref_medecin->prenom)
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function getStatusDisplay(): self
    {
        if ($this->data_model && $this->data_model->_id) {
            $this->_status = $this->data_model->status;
            switch ($this->_status) {
                case InvoiceStatusEnum::VALIDATED()->getValue():
                    $this->_status_icon  = 'fas fa-check';
                    $this->_status_color = '#197837';
                    break;
                case InvoiceStatusEnum::SENT()->getValue():
                    $this->_status_icon  = 'fas fa-envelope';
                    $this->_status_color = '#197837';
                    break;
                case InvoiceStatusEnum::NO_ACK_NEEDED()->getValue():
                case InvoiceStatusEnum::ACCEPTED()->getValue():
                    $this->_status_icon  = 'fas fa-envelope';
                    $this->_status_color = '#00769B';
                    break;
                case InvoiceStatusEnum::REJECTED()->getValue():
                    $this->_status_icon  = 'fas fa-envelope';
                    $this->_status_color = '#9B0000';
                    break;
                case InvoiceStatusEnum::PAID()->getValue():
                    $this->_status_icon  = 'fas fa-money-check-alt';
                    $this->_status_color = '#00769B';
                    break;
                case InvoiceStatusEnum::PAYMENT_REJECTED()->getValue():
                    $this->_status_icon  = 'fas fa-money-check-alt';
                    $this->_status_color = '#9B0000';
                    break;
                default:
            }
        }

        return $this;
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
        /** @var Invoice $entity */
        $view_model = parent::getFromEntity($entity);

        $view_model->rule_forcing                   = $entity->hasRuleForcing() ?
            CRuleForcing::getFromEntity($entity->getRuleForcing()) : null;
        $view_model->care_path                      = CCarePath::getFromEntity($entity->getCarePath());
        $view_model->common_law_accident            = CCommonLawAccident::getFromEntity(
            $entity->getCommonLawAccident()
        );
        $view_model->insurance                      = CInsurance::getFromEntity($entity->getInsurance());
        $view_model->proof_amo                      = $entity->getProofAmo()
            ?  CProofAmo::getFromEntity($entity->getProofAmo()) : null;

        $view_model->prescription                   = $entity->getPrescription()
            ? CJfsePrescription::getFromEntity($entity->getPrescription()) : null;
        $view_model->complementary_health_insurance = $entity->getComplementaryHealthInsurance()
            ? CComplementaryHealthInsurance::getFromEntity($entity->getComplementaryHealthInsurance()) : null;

        $view_model->beneficiary = CBeneficiary::getFromEntity($entity->getBeneficiary());
        $view_model->insured = CInsured::getFromEntity($entity->getInsured());

        $view_model->messages = [];
        foreach ($entity->getMessages() as $message) {
            $view_model->messages[] = CJfseMessage::getFromMessage($message);
        }

        $view_model->questions = [];
        foreach ($entity->getQuestions() as $question) {
            $view_model->questions[] = CJfseQuestion::getFromQuestion($question);
        }

        $view_model->insured_participation_acts = [];
        foreach ($entity->getInsuredParticipationActs() as $pav_act) {
            $view_model->insured_participation_acts[] = CInsuredParticipationAct::getFromEntity($pav_act);
        }

        $view_model->practitioner = CJfseUserView::getFromEntity($entity->getPractitioner());

        $view_model->medical_acts = [];
        foreach ($entity->getMedicalActs() as $medical_act) {
            $view_model->medical_acts[] = CJfseActView::getFromEntity($medical_act);
        }

        $view_model->user_interface = CJfseInvoiceUserInterface::getFromEntity($entity->getUserInterface());
        $view_model->consultation = $entity->getConsultation();
        $view_model->data_model = $entity->loadDataModel();
        $view_model->patient_data_model = $entity->getPatientDataModel();
        $view_model->getStatusDisplay();

        return $view_model;
    }
}
