<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\DataModels;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoiceStatusEnum;
use Ox\Mediboard\Jfse\Exceptions\Invoice\InvoiceException;

/**
 * A data model that make the link between the Mediboard object's, and the invoice from Jfse
 */
final class CJfseInvoice extends CJfseDataModel
{
    /** @var ?int Primary key */
    public ?int $jfse_invoice_id;

    /** @var ?string */
    public ?string $jfse_id;

    /** @var ?int */
    public ?int $consultation_id;

    /** @var ?int */
    public ?int $jfse_user_id;

    /** @var ?int */
    public ?int $jfse_patient_id;

    /** @var ?string */
    public ?string $creation;

    /** @var ?string */
    public ?string $status;

    /** @var ?int */
    public ?int $invoice_number;

    /** @var ?string Indicate that the FSE has a third party payment (either AMO or AMC) */
    public ?string $third_party_payment;

    /** @var ?int The id of the CJfseInvoiceSet */
    public ?int $set_id;

    /** @var ?string The reason the reject of the fse or of the payment by the insurance */
    public ?string $reject_reason;

    /** @var ?CConsultation */
    public ?CConsultation $_consultation;

    /** @var ?CJfseUser */
    public ?CJfseUser $_jfse_user;

    /** @var ?CJfsePatient */
    public ?CJfsePatient $_jfse_patient;

    /** @var CJfseAct[] */
    public ?array $_acts = [];

    /** @var ?CJfseInvoiceSet */
    public ?CJfseInvoiceSet $_set;

    /** @var ?string */
    public ?string $_label;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'jfse_invoices';
        $spec->key   = 'jfse_invoice_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        /* The jfse_id is nullable because we must create the data model before sending the invoice data to Jfse
        *  for setting the integrator's id */
        $props['jfse_id']               = 'str';
        $props['consultation_id']       = 'ref class|CConsultation notNull back|jfse_invoices';
        $props['jfse_user_id']          = 'ref class|CJfseUser notNull back|jfse_invoices';
        $props['jfse_patient_id']       = 'ref class|CJfsePatient back|jfse_invoices';
        $props['set_id']                = 'ref class|CJfseInvoiceSet back|jfse_invoices';
        $props['creation']              = 'dateTime notNull';
        $props['third_party_payment']   = 'bool default|0';
        $props['status']                = InvoiceStatusEnum::getProp();
        $props['invoice_number']        = 'num';
        $props['reject_reason']         = 'str';
        $props['_label']                = 'str';

        return $props;
    }

    /**
     * @return void
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_label = CAppUI::tr('CJfseInvoice-label-pending_invoice');
        if (!$this->isPending() && $this->invoice_number) {
            $this->_label = CAppUI::tr('CJfseInvoice-label-number', $this->invoice_number);
        }
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function store(): ?string
    {
        if (!$this->_id) {
            $this->creation = CMbDT::dateTime();
        }

        return parent::store();
    }

    /**
     * @return CConsultation
     * @throws Exception
     */
    public function loadConsultation(): CConsultation
    {
        if (!$this->_consultation) {
            $this->_consultation = $this->loadFwdRef('consultation_id');
        }

        return $this->_consultation;
    }

    /**
     * @return CJfseUser|null
     * @throws Exception
     */
    public function loadJfseUser(): ?CJfseUser
    {
        if (!$this->_jfse_user) {
            $this->_jfse_user = $this->loadFwdRef('jfse_user_id');
        }

        return $this->_jfse_user;
    }

    /**
     * @return CJfsePatient|null
     * @throws Exception
     */
    public function loadJfsePatient(): ?CJfsePatient
    {
        if (!$this->_jfse_patient) {
            $this->_jfse_patient = $this->loadFwdRef('jfse_patient_id');
        }

        return $this->_jfse_patient;
    }


    /**
     * @return CJfseAct[]
     * @throws Exception
     */
    public function loadActs(): array
    {
        if (!$this->_acts) {
            $this->_acts = $this->loadBackRefs('jfse_acts');

            if (is_array($this->_acts)) {
                /** @var CJfseAct $act_link */
                foreach ($this->_acts as $act_link) {
                    $act_link->loadAct();
                }
            } else {
                $this->_acts = [];
            }
        }

        return $this->_acts;
    }

    /**
     * @param int $consultation_id
     *
     * @return $this
     * @throws InvoiceException
     */
    public function setConsultationId(int $consultation_id): self
    {
        try {
            $consultation = CConsultation::findOrFail($consultation_id);
        } catch (Exception $e) {
            throw InvoiceException::consultationNotFound($consultation_id, $e);
        }

        $this->consultation_id = $consultation->_id;

        return $this;
    }

    /**
     * @param int $jfse_user_id
     *
     * @return $this
     * @throws InvoiceException
     */
    public function setJfseUserId(int $jfse_user_id): self
    {
        try {
            $user = CJfseUser::findOrFail($jfse_user_id);
        } catch (Exception $e) {
            throw InvoiceException::userNotFound($jfse_user_id, $e);
        }

        $this->jfse_user_id = $user->_id;

        return $this;
    }

    /**
     * @param int $jfse_patient_id
     *
     * @return $this
     * @throws InvoiceException
     */
    public function setJfsePatientId(int $jfse_patient_id): self
    {
        try {
            $patient = CJfsePatient::findOrFail($jfse_patient_id);
        } catch (Exception $e) {
            throw InvoiceException::patientNotFound($jfse_patient_id, $e);
        }

        $this->jfse_patient_id = $patient->_id;

        return $this;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function setLabel(): void
    {
        $this->updateFormFields();
        $this->loadActs();

        if (is_array($this->_acts) && count($this->_acts)) {
            $this->_label .= " -";
            foreach ($this->_acts as $act_link) {
                switch ($act_link->_act->_class) {
                    case 'CActeCCAM':
                        $this->_label .= " {$act_link->_act->code_acte}";
                        break;
                    default:
                        $this->_label .= " {$act_link->_act->code}";
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === InvoiceStatusEnum::PENDING()->getValue();
    }

    /**
     * @return bool
     */
    public function isValidated(): bool
    {
        return $this->status === InvoiceStatusEnum::VALIDATED()->getValue();
    }

    /**
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === InvoiceStatusEnum::REJECTED()->getValue()
            || $this->status === InvoiceStatusEnum::PAYMENT_REJECTED()->getValue();
    }

    /**
     * @param string $jfse_id
     *
     * @return CJfseInvoice
     * @throws Exception
     */
    public static function getFromJfseId(string $jfse_id): CJfseInvoice
    {
        $invoice          = new self();
        $invoice->jfse_id = $jfse_id;
        $invoice->loadMatchingObject();

        return $invoice;
    }

    /**
     * @param CJfseUser $user
     * @param int       $number
     *
     * @return CJfseInvoice
     * @throws Exception
     */
    public static function getFromUserAndNumber(CJfseUser $user, int $number): self
    {
        $invoice = new self();
        $invoice->jfse_user_id = $user->_id;
        $invoice->invoice_number = $number;
        $invoice->loadMatchingObject();

        return $invoice;
    }

    /**
     * @param CConsultation $consultation
     *
     * @return CJfseInvoice[]
     * @throws Exception
     */
    public static function getValidatedInvoicesFromConsultation(CConsultation $consultation): array
    {
        $invoices = $consultation->loadBackRefs(
            'jfse_invoices',
            null,
            null,
            null,
            null,
            null,
            '',
            [
                'status' => CSQLDataSource::prepareIn([
                    InvoiceStatusEnum::VALIDATED()->getValue(),
                    InvoiceStatusEnum::SENT()->getValue(),
                    InvoiceStatusEnum::ACCEPTED()->getValue(),
                    InvoiceStatusEnum::PAID()->getValue(),
                    InvoiceStatusEnum::NO_ACK_NEEDED()->getValue(),
                ])
            ]
        );

        if (is_null($invoices)) {
            $invoices = [];
        }

        return $invoices;
    }

    /**
     * Load the Invoices linked to the given consultation
     *
     * @param CConsultation $consultation
     * @throws Exception
     */
    public function loadIdsFSE(CConsultation $consultation): void
    {
        if ($consultation->_id) {
            $invoices = $this->loadList([
                'consultation_id' => " = {$consultation->_id}",
                'status'          => " != 'pending'",
            ], 'invoice_number DESC');

            if (!empty($invoices)) {
                $consultation->_current_fse = reset($invoices);
            }
        }
    }

    /**
     * The declaration of this function is mandatory, it is used in the facturations views
     *
     * @param CConsultation $consultation
     */
    public function makeFSE(CConsultation $consultation): void
    {
        return;
    }
}
