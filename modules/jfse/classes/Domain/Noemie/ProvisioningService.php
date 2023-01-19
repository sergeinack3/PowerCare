<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Noemie;

use DateTime;
use DateTimeImmutable;
use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Jfse\ApiClients\HistoryClient;
use Ox\Mediboard\Jfse\ApiClients\InvoicingClient;
use Ox\Mediboard\Jfse\DataModels\CJfseAct;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\Invoicing\Invoice;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoiceStatusEnum;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalActTypeEnum;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\Exceptions\ApiException;
use Ox\Mediboard\Jfse\Exceptions\DataModelException;
use Ox\Mediboard\Jfse\Mappers\InvoicingMapper;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\SalleOp\CActeCCAM;

/**
 * A service that can create object (consultation, acts, patient, invoices) from the invoice history
 */
final class ProvisioningService extends AbstractService
{
    /** @var HistoryClient The API Client */
    protected $client;

    /** @var InvoicingClient The API Client */
    protected InvoicingClient $invoicing_client;

    protected CJfseUser $user;

    protected int $year;

    /** @var CPlageconsult[] */
    protected array $ranges = [];

    protected array $results;

    /** @var array */
    protected static array $months = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'November',
        'December',
    ];

    /**
     * InvoicingService constructor.
     *
     * @param HistoryClient|null $client
     */
    public function __construct(HistoryClient $client = null, InvoicingClient $invoicing_client = null)
    {
        parent::__construct($client ?? new HistoryClient());

        $this->invoicing_client = $invoicing_client ?? new InvoicingClient();
        $this->invoicing_client->setErrorsHandler([$this, 'handleErrors']);
        $this->invoicing_client->setMessagesHandler([$this, 'handleMessages']);

        $this->results = [
            'CPlageconsult' => 0,
            'CPatient'      => 0,
            'CJfsePatient'  => 0,
            'CConsultation' => 0,
            'CJfseInvoice'  => 0,
            'CActeNGAP'     => 0,
            'CActeCCAM'     => 0,
            'CJfseAct'      => 0,
            'errors'        => [],
        ];
    }

    /**
     * Provision the data from the invoices history, for the given user and the given year
     *
     * @param CJfseUser $user
     * @param int|null  $year
     *
     * @return array
     */
    public function provisionDataForUser(CJfseUser $user, int $year = null): array
    {
        $this->user = $user;
        $this->user->loadMediuser();

        if (!$year) {
            $year = (int)(new DateTimeImmutable())->format('Y');
        }

        $this->year = $year;

        foreach (self::$months as $month) {
            $this->provisionDataForMonth($month);
        }

        return $this->results;
    }

    /**
     * Provision the data from the invoices history, for the given month
     *
     * @param string $month
     *
     * @return void
     */
    protected function provisionDataForMonth(string $month): void
    {
        $begin = new DateTimeImmutable("First day of {$month} {$this->year}");
        $end   = new DateTimeImmutable("Last day of {$month} {$this->year}");

        $history = CMbArray::get($this->client->getInvoiceHistory($begin, $end)->getContent(), 'lstFactures', []);
        foreach ($history as $entry) {
            try {
                $invoice = InvoicingMapper::getInvoiceFromResponse(
                    $this->invoicing_client->getDonneesFacture($entry['idFacture'])
                );

                $this->provisionDataFromInvoice($invoice);
            } catch (ApiException $e) {
                $this->results['errors'][] = $e->getLocalizedMessage() . "IdFacture: {$entry['idFacture']}";
            }
        }
    }

    /**
     * Parse the invoice data and creates the objects in the database
     *
     * @param Invoice $invoice
     *
     * @return void
     */
    protected function provisionDataFromInvoice(Invoice $invoice): void
    {
        try {
            $range        = $this->getConsultRangeFromDate($invoice->getCreationDate());
            $patient      = $this->getPatient($invoice->getBeneficiary(), $invoice->getInsured());
            $jfse_patient = $this->getJfsePatientFromPatient($patient);

            $consult            = $this->createConsultation($range, $patient, $invoice);
            $invoice_data_model = $this->createJfseInvoice($invoice, $consult, $jfse_patient);
            $this->createActs($invoice, $consult, $invoice_data_model);
            $this->validateConsultation($consult);
        } catch (DataModelException $e) {
            $this->results['errors'][] = $e->getMessage();
        }
    }

    /**
     * Returns the CPlageconsult for the given date if it exists, or creates it otherwise
     *
     * @param DateTime $date
     *
     * @return CPlageconsult
     */
    protected function getConsultRangeFromDate(DateTime $date): CPlageconsult
    {
        if (!array_key_exists($date->format('Y-m-d'), $this->ranges)) {
            $range                                = $this->createCPlageConsult($date);
            $this->ranges[$date->format('Y-m-d')] = $range;
        } else {
            $range = $this->ranges[$date->format('Y-m-d')];
        }

        return $range;
    }

    /**
     * Creates a CPlageconsult for the user on the given date
     *
     * @param DateTime $date
     *
     * @return CPlageconsult
     * @throws DataModelException
     */
    protected function createCPlageConsult(DateTime $date): CPlageconsult
    {
        $range          = new CPlageconsult();
        $range->date    = $date->format('Y-m-d');
        $range->chir_id = $this->user->_mediuser->_id;
        $range->freq    = '00:05:00';
        $range->debut   = '08:00:00';
        $range->fin     = '20:00:00';
        try {
            $range->loadMatchingObject();
        } catch (Exception $e) {
            throw DataModelException::persistenceError($e->getMessage());
        }

        if (!$range->_id) {
            $this->storeObject($range);
            $this->results['CPlageconsult']++;
        }

        return $range;
    }

    /**
     * Get the patient, or create it if it doesn't exist
     *
     * @param Beneficiary $beneficiary
     *
     * @return CPatient
     * @throws DataModelException
     */
    protected function getPatient(Beneficiary $beneficiary, Insured $insured): CPatient
    {
        $patient                    = new CPatient();
        $patient->nom               = $beneficiary->getPatient()->getLastName();
        $patient->prenom            = $beneficiary->getPatient()->getFirstName();
        $patient->naissance         = $beneficiary->getPatient()->getBirthDate();
        $patient->matricule         = $beneficiary->getFullCertifiedNir()
            ?: $insured->getNir() . $insured->getNirKey();
        $patient->sexe              = substr($patient->matricule, 0, 1) === '1' ? 'm' : 'f';
        $patient->code_regime       = $insured->getRegimeCode();
        $patient->caisse_gest       = $insured->getManagingFund();
        $patient->centre_gest       = $insured->getManagingCenter();
        $patient->code_gestion      = $insured->getManagingCode();
        $patient->cp                = '17000';
        $patient->rang_naissance    = $beneficiary->getPatient()->getBirthRank();
        $patient->qual_beneficiaire = $beneficiary->getQuality() ?? '00';

        if (strpos($patient->nom, "'") !== false) {
            $patient->nom = str_replace("'", "\'", $patient->nom);
        }

        try {
            $patient->loadMatchingObjectEsc();
        } catch (Exception $e) {
            throw DataModelException::persistenceError($e->getMessage());
        }

        if (!$patient->_id) {
            $this->storeObject($patient);
            $this->results['CPatient']++;
        }

        return $patient;
    }


    /**
     * Get the CJfsePatient from the CPatient, or create it if it doesn't exist
     *
     * @param CPatient $patient
     *
     * @return CJfsePatient
     * @throws DataModelException
     */
    protected function getJfsePatientFromPatient(CPatient $patient): CJfsePatient
    {
        try {
            $jfse_patient = $patient->loadUniqueBackRef('jfse_patient');
        } catch (Exception $e) {
            $jfse_patient = null;
        }

        if (is_null($jfse_patient) || !$jfse_patient->_id) {
            $jfse_patient                      = new CJfsePatient();
            $jfse_patient->patient_id          = $patient->_id;
            $jfse_patient->nir                 = $patient->matricule;
            $jfse_patient->birth_date          = $patient->naissance;
            $jfse_patient->birth_rank          = $patient->rang_naissance;
            $jfse_patient->quality             = $patient->qual_beneficiaire;
            $jfse_patient->last_name           = $patient->nom;
            $jfse_patient->first_name          = $patient->prenom;
            $jfse_patient->amo_regime_code     = $patient->code_regime;
            $jfse_patient->amo_managing_fund   = $patient->caisse_gest;
            $jfse_patient->amo_managing_center = $patient->centre_gest;
            $jfse_patient->amo_managing_code   = $patient->code_gestion;

            $jfse_patient->loadMatchingObjectEsc();

            $this->storeObject($jfse_patient);

            $this->results['CJfsePatient']++;
        }

        return $jfse_patient;
    }

    /**
     * @param CPlageconsult $range
     * @param CPatient      $patient
     *
     * @return CConsultation
     * @throws DataModelException
     */
    protected function createConsultation(CPlageconsult $range, CPatient $patient, Invoice $invoice): CConsultation
    {
        $consultation                  = new CConsultation();
        $consultation->owner_id        = $this->user->_mediuser->_id;
        $consultation->plageconsult_id = $range->_id;
        $consultation->patient_id      = $patient->_id;
        $consultation->duree           = 1;
        $consultation->chrono          = 64;

        /* Set the pricings */
        $consultation->du_patient      = $invoice->getTotalInsured();
        $consultation->du_tiers        = $invoice->getAmountOwedAmo() + $invoice->getAmountOwedAmc();
        $consultation->total_amo       = $invoice->getTotalAmo();
        $consultation->total_amc       = $invoice->getTotalAmc();
        $consultation->total_assure    = $invoice->getTotalInsured();

        /* Those properties must be initialized */
        $consultation->_codes_ccam     = [];
        $consultation->_tokens_ccam    = [];
        $consultation->_tokens_ngap    = [];

        try {
            $slots = $range->getEmptySlots();
            if (!count($slots)) {
                throw DataModelException::persistenceError('No empty slots on CPlageconsult');
            }
            $slot                = reset($slots);
            $consultation->heure = $slot['hour'];
        } catch (Exception $e) {
            throw DataModelException::persistenceError($e->getMessage());
        }

        $this->storeObject($consultation);

        $this->results['CConsultation']++;

        return $consultation;
    }

    /**
     * @param Invoice       $invoice
     * @param CConsultation $consultation
     * @param CJfsePatient  $patient
     *
     * @return CJfseInvoice
     * @throws DataModelException
     */
    protected function createJfseInvoice(
        Invoice $invoice,
        CConsultation $consultation,
        CJfsePatient $patient
    ): CJfseInvoice {
        $jfse_invoice          = new CJfseInvoice();
        $jfse_invoice->jfse_id = $invoice->getId();
        $jfse_invoice->consultation_id = $consultation->_id;
        $jfse_invoice->jfse_user_id    = $this->user->_id;
        $jfse_invoice->jfse_patient_id = $patient->_id;

        try {
            $jfse_invoice->loadMatchingObjectEsc();
        } catch (Exception $e) {
        }

        if (!$jfse_invoice->_id) {
            $jfse_invoice->status          = InvoiceStatusEnum::VALIDATED()->getValue();
            $jfse_invoice->invoice_number  = $invoice->getInvoiceNumber();

            $this->storeObject($jfse_invoice);

            $this->results['CJfseInvoice']++;
        }

        return $jfse_invoice;
    }

    /**
     * @param Invoice       $invoice
     * @param CConsultation $consultation
     * @param CJfseInvoice  $data_model
     *
     * @return void
     * @throws DataModelException
     */
    protected function createActs(Invoice $invoice, CConsultation $consultation, CJfseInvoice $data_model): void
    {
        foreach ($invoice->getMedicalActs() as $act) {
            switch ($act->getType()) {
                case MedicalActTypeEnum::CCAM():
                    $cacte                   = new CActeCCAM();
                    $cacte->code_acte        = $act->getActCode();
                    $cacte->code_activite    = $act->getActivityCode();
                    $cacte->code_phase       = $act->getPhaseCode();
                    $cacte->code_association = $act->getAssociationCode();
                    $cacte->_modificateurs   = $act->getModifiers();

                    /* Prevents the CCodageCCAM rule to be guessed, which store the CActeCCAM again,
                     *  and execute the CJfseActHandler */
                    $cacte->_update_codage_rule = false;

                    /* Add the code to the list of ccam code on the consultation */
                    $consultation->_codes_ccam[] = $cacte->code_acte;
                    $consultation->updateCCAMPlainField();
                    $this->storeObject($consultation);
                    break;
                default:
                    $cacte              = new CActeNGAP();
                    $cacte->code        = $act->getActCode();
                    $cacte->coefficient = $act->getCoefficient();
                    $cacte->quantite    = $act->getQuantity();
                    $cacte->complement  = $act->getAdditional();
            }

            $cacte->execution            = $act->getDate()->format('Y-m-d h:i:s');
            $cacte->executant_id         = $this->user->_mediuser->_id;
            $cacte->object_class         = $consultation->_class;
            $cacte->object_id            = $consultation->_id;
            $cacte->montant_depassement  = $act->getPricing()->getExceedingAmount();
            $cacte->montant_base         = $act->getPricing()->getTotalAmount()
                - $act->getPricing()->getExceedingAmount();

            /* Prevent the CJfseActHandler to be called */
            $cacte->_ignore_eai_handlers = true;

            $consultation->secteur1      += $cacte->montant_base;
            $consultation->secteur2      += $cacte->montant_depassement;

            $this->storeObject($cacte);

            $this->results[$cacte->_class]++;

            $this->createCJfseAct($act->getId(), $cacte, $data_model);
        }
    }

    /**
     * @param string|null  $jfse_id
     * @param CActe        $acte
     * @param CJfseInvoice $invoice
     *
     * @return void
     * @throws DataModelException
     */
    protected function createCJfseAct(?string $jfse_id, CActe $acte, CJfseInvoice $invoice): void
    {
        $data_model                  = new CJfseAct();
        $data_model->jfse_id         = $jfse_id;
        $data_model->jfse_invoice_id = $invoice->_id;
        $data_model->act_class       = $acte->_class;
        $data_model->act_id          = $acte->_id;

        $this->storeObject($data_model);

        $this->results['CJfseAct']++;
    }

    /**
     * @param CConsultation $consultation
     *
     * @return void
     */
    protected function validateConsultation(CConsultation $consultation): void
    {
        $consultation->valide = '1';
        $this->storeObject($consultation);
    }

    /**
     * @param CMbObject $object
     *
     * @return void
     * @throws DataModelException
     */
    protected function storeObject(CMbObject $object): void
    {
        try {
            $msg = $object->store();
        } catch (Exception $e) {
            throw DataModelException::persistenceError($e->getMessage());
        }

        if ($msg) {
            throw DataModelException::persistenceError($msg);
        }
    }
}
