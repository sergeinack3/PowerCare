<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Mediboard\Ameli\ApCVHandler;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Jfse\Api\Message;
use Ox\Mediboard\Jfse\Api\Question;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\ApiClients\InvoicingClient;
use Ox\Mediboard\Jfse\DataModels\CJfseAct;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\AbstractService;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalAct;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalActService;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\Physician;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserManagementService;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvAcquisitionModeEnum;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvService;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;
use Ox\Mediboard\Jfse\Exceptions\ApiMessageException;
use Ox\Mediboard\Jfse\Exceptions\JfseException;
use Ox\Mediboard\Jfse\Mappers\ApCvMapper;
use Ox\Mediboard\Jfse\Mappers\InvoicingMapper;
use Ox\Mediboard\Jfse\Mappers\VitalCardMapper;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

class InvoicingService extends AbstractService
{
    /** @var InvoicingClient The API Client */
    protected $client;

    /** @var string The value of the field source_library of the messages that concerns the common law accident */
    protected const COMMON_LAW_ACCIDENT_ERROR_SOURCE = 'ACCIDENT DE DROIT COMMUN';

    /** @var string  */
    public const API_ERROR_UNKNOW_INVOICE_TEXT = 'Numéro de facture inexistant';

    /**
     * InvoicingService constructor.
     *
     * @param InvoicingClient|null $client
     */
    public function __construct(InvoicingClient $client = null)
    {
        parent::__construct($client ?? new InvoicingClient());
        $this->client->setMessagesHandler([$this, 'handleErrorMessagesOnly']);
    }

    /**
     * @param CConsultation $consultation
     * @param string|null      $invoice_id
     *
     * @return Invoice
     */
    public function viewInvoice(CConsultation $consultation, string $invoice_id = null): Invoice
    {
        if (self::consultationHasInvoices($consultation)) {
            $invoice = $this->getInvoiceFromConsultation($consultation, $invoice_id);
        } else {
            $invoice = $this->initializeInvoice($consultation);
        }

        $invoice->loadDataModel();
        $invoice->setConsultation($consultation);

        $this->prepareInvoice($invoice);

        return $invoice;
    }

    public static function getInvoiceDataModelFromConsultation(CConsultation $consultation): CJfseInvoice
    {
        /* If no invoice is selected, we first check if there are pending invoices */
        if (self::consultationHasPendingInvoices($consultation)) {
            $invoice_data_model = self::getFirstPendingInvoiceFromConsultation($consultation);
        } else {
            /* Then we get the last validated invoice */
            $invoice_data_model = self::getFirstValidatedInvoiceFromConsultation($consultation);
        }

        return $invoice_data_model;
    }

    public function getInvoiceFromConsultation(CConsultation $consultation, string $invoice_id = null): Invoice
    {
        /* If no invoice id is selected, we will get the first invoice linked to the consultation */
        if (!$invoice_id) {
            $invoice_data_model = self::getInvoiceDataModelFromConsultation($consultation);
        } else {
            $invoice_data_model = CJfseInvoice::getFromJfseId($invoice_id);
        }

        return InvoicingMapper::getInvoiceFromResponse(
            $this->client->getDonneesFacture($invoice_data_model->jfse_id)
        );
    }

    public function getInvoice(string $invoice_id = null): Invoice
    {
        $invoice_data_model = CJfseInvoice::getFromJfseId($invoice_id);

        return InvoicingMapper::getInvoiceFromResponse(
            $this->client->getDonneesFacture($invoice_data_model->jfse_id)
        );
    }

    public function createInvoice(
        CConsultation $consultation,
        ?SecuringModeEnum $securing_mode,
        ?string $situation_code,
        ?string $vitale_nir,
        bool $apcv = false
    ): Invoice {
        $invoice = $this->initializeInvoice($consultation, $securing_mode, $situation_code, $vitale_nir, $apcv);

        $invoice->setConsultation($consultation);

        $this->prepareInvoice($invoice);

        return $invoice;
    }

    public function initializeInvoice(
        CConsultation $consultation,
        SecuringModeEnum $securing = null,
        string $situation_code = null,
        string $vitale_nir = null,
        bool $apcv = false
    ): Invoice {
        $user = CJfseUser::getFromMediuser($consultation->loadRefPraticien());

        $invoice = new Invoice();
        $invoice->setAutomaticDeletion(false);
        $invoice->setConsultation($consultation);

        if (!$securing) {
            $invoice->setDefaultSecuringMode();
        } else {
            $invoice->setSecuring($securing);
        }

        $invoice->setMedicalActsFromConsultation();
        if ($apcv) {
            $card = (new ApCvService())->getVitalCard();

            $invoice->setBeneficiaryFromApCv($consultation->loadRefPatient(), $card);
        } else {
            $invoice->setBeneficiaryFromPatient($consultation->loadRefPatient(), $situation_code, $vitale_nir);
        }
        /* The data model is created before the initialization of the invoice to send an integrator id to Jfse */
        $invoice->createDataModel();
        $invoice_data_model = $invoice->getDataModel();

        try {
            $invoice = InvoicingMapper::getInvoiceFromResponse($this->client->initialiserFacture($invoice));
        } catch (JfseException $e) {
            $invoice_data_model->delete();
            throw $e;
        }

        /* Creation of the data models */
        if ($invoice_data_model->_id && !$invoice_data_model->jfse_id) {
            $invoice_data_model->jfse_id = $invoice->getId();
            $invoice_data_model->store();

            /* Creates the data models of the MedicalActs */
            foreach ($invoice->getMedicalActs() as $medical_act) {
                $medical_act->createDataModel($invoice->getId());
            }
        }

        $invoice->loadDataModel();

        /* Authorize the creation of new acts */
        if ($consultation->valide) {
            $consultation->valide = '0';
            $consultation->store();
        }

        return $invoice;
    }

    /**
     * Change the securing mode of the invoice by cancelling it and recreating it
     *
     * @param string           $invoice_id
     * @param SecuringModeEnum $mode
     * @param string           $situation_code
     *
     * @return Invoice
     * @throws \Exception
     */
    public function selectSecuringMode(
        string $invoice_id,
        SecuringModeEnum $mode,
        string $situation_code = null
    ): Invoice {
        $invoice = InvoicingMapper::getInvoiceFromResponse(
            $this->client->getDonneesFacture($invoice_id)
        );
        $data_model = $invoice->loadDataModel();
        foreach ($invoice->getMedicalActs() as $medical_act) {
            $medical_act->loadDataModel();
        }

        $this->client->annulerFacture($invoice->getId());
        $invoice->changeSecuringMode($mode);
        $invoice->setBeneficiaryFromPatient($data_model->loadConsultation()->loadRefPatient(), $situation_code);

        try {
            $invoice = InvoicingMapper::getInvoiceFromResponse($this->client->initialiserFacture($invoice));
            $invoice->setConsultation($data_model->loadConsultation());
            $invoice->setBeneficiaryFromPatient($data_model->loadConsultation()->loadRefPatient());
            $data_model->jfse_id = $invoice->getId();
            $data_model->store();

            $data_model->loadActs();
            /* Delete the previous medical act data models because the jfse_id change when a new invoice is created */
            foreach ($data_model->_acts as $act) {
                $act->delete();
            }

            foreach ($invoice->getMedicalActs() as $medical_act) {
                $medical_act->createDataModel($invoice->getId());
            }
        } catch (Exception $e) {
            /* In case of an exception (API or something else), the invoice data model, which has no jfse id yet,
             *  is deleted, to ensure that a new invoice can be initialized */
            $data_model->delete();
            throw $e;
        }

        $this->prepareInvoice($invoice);

        return $invoice;
    }

    /**
     * Cancel the current invoice, and recreates it in ApCv mode
     *
     * @param string $invoice_id
     *
     * @return Invoice
     */
    public function switchInvoiceToApCv(string $invoice_id, VitalCard $apcv_card): Invoice
    {
        $invoice = InvoicingMapper::getInvoiceFromResponse(
            $this->client->getDonneesFacture($invoice_id)
        );
        $data_model = $invoice->loadDataModel();
        foreach ($invoice->getMedicalActs() as $medical_act) {
            $medical_act->loadDataModel();
        }

        $this->client->annulerFacture($invoice->getId());
        $invoice->setConsultation($data_model->loadConsultation());

        /* The ApCv is only authorized for the Secured and Desynchronized modes */
        if (
            $invoice->getSecuring() !== SecuringModeEnum::DESYNCHRONIZED()
            && $invoice->getSecuring() !== SecuringModeEnum::SECURED()
        ) {
            $invoice->setDefaultSecuringMode();
        }

        $invoice->setBeneficiaryFromApCv($data_model->loadConsultation()->loadRefPatient(), $apcv_card);

        try {
            $invoice = InvoicingMapper::getInvoiceFromResponse($this->client->initialiserFacture($invoice));
            $invoice->setConsultation($data_model->loadConsultation());
            $invoice->setBeneficiaryFromPatient($data_model->loadConsultation()->loadRefPatient());
            $data_model->jfse_id = $invoice->getId();
            $data_model->store();

            $data_model->loadActs();
            /* Delete the previous medical act data models because the jfse_id change when a new invoice is created */
            foreach ($data_model->_acts as $act) {
                $act->delete();
            }

            foreach ($invoice->getMedicalActs() as $medical_act) {
                $medical_act->createDataModel($invoice->getId());
            }
        } catch (Exception $e) {
            /* In case of an exception (API or something else), the invoice data model, which has no jfse id yet,
             *  is deleted, to ensure that a new invoice can be initialized */
            $data_model->delete();
            throw $e;
        }

        $this->prepareInvoice($invoice);

        return $invoice;
    }

    public function setCommonLawAccident(CommonLawAccident $common_law_accident, string $invoice_id): bool
    {
        $this->client->setMessagesHandler(
            [$this, 'handleErrorAndWarningMessagesForSourcesLibraryOnly'],
            [self::COMMON_LAW_ACCIDENT_ERROR_SOURCE]
        );

        $this->client->setAccidentDC($common_law_accident, $invoice_id);

        return true;
    }

    public function setThirdPartyPayment(string $invoice_id, ComplementaryHealthInsurance $insurance): Invoice
    {
        $response = $this->client->setOrganismeComplementaire($invoice_id, $insurance);

        return InvoicingMapper::getInvoiceFromResponse($response);
    }

    public function selectConvention(string $invoice_id, string $convention_id): Invoice
    {
        $invoice = $this->getInvoice($invoice_id);
        $complementary = $invoice->getComplementaryHealthInsurance();

        $convention = $complementary->getAssistant()->getConventionFromApplicableConventions($convention_id);
        $complementary->selectConvention($convention);

        return InvoicingMapper::getInvoiceFromResponse(
            $this->client->setOrganismeComplementaire($invoice_id, $complementary)
        );
    }

    public function selectFormula(string $invoice_id, string $formula_number, array $parameters): Invoice
    {
        $invoice = $this->getInvoice($invoice_id);
        $complementary = $invoice->getComplementaryHealthInsurance();

        $formula = $complementary->getAssistant()->getFormulaFromApplicableFormulas($formula_number);
        $formula->setParametersFromArray($parameters);
        $complementary->selectFormula($formula);

        return InvoicingMapper::getInvoiceFromResponse(
            $this->client->setOrganismeComplementaire($invoice_id, $complementary)
        );
    }

    public function validateInvoice(string $invoice_id): Invoice
    {
        $this->client->setMessagesHandler([$this, 'handleValidateInvoiceWarnings']);
        $response = $this->client->validerFacture($invoice_id);

        $invoice = InvoicingMapper::getInvoiceFromResponse($response);
        $invoice->loadDataModel();
        $content = $response->getContent();

        if (array_key_exists('facturer', $content) && $content['facturer'] === '1') {
            $invoice->validate();
            $this->synchronizeCActes($invoice);
            self::updateConsultationAfterInvoiceValidation($invoice);
        }

        return $invoice;
    }

    public function updateConsultationAfterInvoiceValidation(Invoice $invoice): bool
    {
        $consultation = $invoice->getDataModel()->loadConsultation();
        $invoice->setConsultation($consultation);
        $invoice_data_models = CJfseInvoice::getValidatedInvoicesFromConsultation($consultation);
        $invoices = [$invoice];
        foreach ($invoice_data_models as $invoice_data_model) {
            if ($invoice_data_model->jfse_id !== $invoice->getId()) {
                $invoices[] = $this->getInvoiceFromConsultation($consultation, $invoice_data_model->jfse_id);
            }
        }

        if ($consultation->valide == 1) {
            $consultation->valide = '0';
            $consultation->store();
        }

        /* Update the different fields of the consultation that contains the amounts and prices */
        $consultation->du_patient = 0;
        $consultation->du_tiers = 0;
        $consultation->total_amc = 0;
        $consultation->total_amo = 0;
        $consultation->total_assure = 0;
        $linked_acts_count = 0;

        foreach ($invoices as $invoice) {
            $consultation->du_patient += $invoice->getTotalInsured();
            $consultation->du_tiers += $invoice->getAmountOwedAmo() + $invoice->getAmountOwedAmc();
            $consultation->total_amo += $invoice->getTotalAmo();
            $consultation->total_amc += $invoice->getTotalAmc();
            $consultation->total_assure += $invoice->getTotalInsured();

            $linked_acts_count += $invoice->loadDataModel()->countBackRefs('jfse_acts');
        }

        if ($consultation->secteur3) {
            $consultation->du_patient += $consultation->secteur3 + $consultation->du_tva;
            $consultation->total_assure += $consultation->secteur3 + $consultation->du_tva;
        }

        $consultation->loadRefsActes();
        /* Validate the consultation if all the invoices are validated and all the acts are linked */
        if (
            count($invoices) === (int)$consultation->countBackRefs('jfse_invoices')
            && $linked_acts_count === count($consultation->_ref_actes)
        ) {
            $consultation->valide = '1';
        }

        $consultation->store();

        return true;
    }

    public function cancelInvoice(string $invoice_id): bool
    {
        $this->client->annulerFacture($invoice_id);

        $invoice = Invoice::hydrate(['id' => $invoice_id]);
        return $invoice->deleteDataModel();
    }

    /**
     * Deletes the Invoice from the JFSE server and also deletes the data model
     *
     * @param string $invoice_id
     *
     * @return bool
     * @throws Exception
     */
    public function deleteInvoice(string $invoice_id): bool
    {
        $this->client->supprimerFacture($invoice_id);

        return $this->deleteInvoiceDataModel($invoice_id);
    }

    /**
     * Deletes the data model of the Invoice with the given jfse_id
     *
     * @param string $invoice_id
     *
     * @return bool
     * @throws Exception
     */
    public function deleteInvoiceDataModel(string $invoice_id): bool
    {
        $invoice = Invoice::hydrate(['id' => $invoice_id]);
        $invoice->loadDataModel();

        /* If the consultation has no other validated invoices, the creation of acts is enabled again */
        $consultation = $invoice->getDataModel()->loadConsultation();
        if (
            $consultation->countBackRefs('jfse_invoices', [
                'status' => " = 'validated'",
                'jfse_invoice_id' => "!= {$invoice->getDataModel()->_id}"
            ]) == 0
        ) {
            $consultation->valide = '0';
            $consultation->store();
        }

        return $invoice->deleteDataModel();
    }

    public function getPrescriptionFromInvoice(string $invoice_id): Prescription
    {
        $invoice = InvoicingMapper::getInvoiceFromResponse(
            $this->client->getDonneesFacture($invoice_id)
        );

        $prescription = $invoice->getPrescription() ?? Prescription::hydrate(['prescriber' => new Physician()]);
        $prescription->setInvoiceId($invoice_id);

        return $prescription;
    }

    public function getChildrenConsultationAssistant(
        string $invoice_id,
        string $reference_date,
        bool $enforceable_tariff = null,
        bool $referring_physician = null
    ): bool {
        $response = $this->client->getAideConsultationEnfant(
            $invoice_id,
            CMbDT::format($reference_date, '%Y%m%d'),
            $enforceable_tariff,
            $referring_physician
        );

        $result = false;
        if (($code = CMbArray::get($response->getContent(), 'code', '')) !== '') {
            $act = new CActeNGAP();
            $act->code = $code;
            $act->coefficient = 1;
            $act->quantite = 1;
            $act->execution = $reference_date . ' ' . CMbDT::time();

            $invoice = CJfseInvoice::getFromJfseId($invoice_id);
            $consultation = $invoice->loadConsultation();

            $act->object_class = $consultation->_class;
            $act->object_id = $consultation->_id;

            $act->executant_id = $consultation->getExecutantId();
            /* The act is sent to jFSE through the JfseActHandler */
            $act->store();
            $result = true;
        }

        return $result;
    }

    public function anonymize(string $invoice_id): bool
    {
        $this->client->setMessagesHandler(
            function (Response $response): void {
                return;
            }
        );
        $response = $this->client->setAnonymiser($invoice_id, 3);

        return true;
    }

    public function answerQuestions(string $invoice_id, array $questions_data): bool
    {
        $questions = [];
        foreach ($questions_data as $data) {
            /* Indicate that the consultation concerns the long lasting affliction accordingly to the answer */
            if ($data['nature'] == '1') {
                $invoice = Invoice::hydrate(['id' => $invoice_id]);
                $invoice->setLongLastingAffliction((bool)$data['answer']);
            } elseif ($data['nature'] == '7') {
                $data['answer'] = CMbDT::format($data['answer'], '%Y%m%d');
            }

            $questions[] = new Question($data['id'], (int)$data['nature'], '', 0, [], $data['answer']);
        }

        $response = $this->client->setReponseQuestions($invoice_id, $questions);
        $invoice = InvoicingMapper::getInvoiceFromResponse($response);

        $result = false;

        if (!count($invoice->getQuestions())) {
            $result = true;
        }

        return true;
    }

    public function forceRule(string $invoice_id, RuleForcing $rule_forcing): bool
    {
        switch ($rule_forcing->getForcingType()) {
            case RuleForcing::COMPLETE_CONTROL_FORCING:
                $this->client->setForcageReglesCC($invoice_id, $rule_forcing);
                break;
            default:
                $this->client->setForcageReglesSTD($invoice_id, $rule_forcing);
        }

        return true;
    }

    public function setInsuredParticipation(string $invoice_id, InsuredParticipationAct $participation): bool
    {
        $this->client->setPav($invoice_id, $participation);

        return true;
    }

    public function getComplements(string $invoice_id): ?Complement
    {
        $complement = InvoicingMapper::getComplementFromResponse($this->client->setGestionsComplement($invoice_id));

        return $complement;
    }

    private function prepareInvoice(Invoice $invoice): void
    {
        if ($invoice->getPractitioner()) {
            $invoice->setPractitioner((new UserManagementService())->getUser($invoice->getPractitioner()->getId()));
            $invoice->getPractitioner()->loadDataModel();

            $this->displayTreatmentType($invoice);
        }

        $this->checkApCVDemo($invoice);

        $this->checkAnonymize($invoice);
    }

    public function setTreatmentType(string $invoice_id, int $treament_type): bool
    {
        $this->client->setTypeTraitement($invoice_id, $treament_type);

        return true;
    }

    private function synchronizeCActes(Invoice $invoice): void
    {
        $data_model = $invoice->loadDataModel();

        foreach ($invoice->getMedicalActs() as $medical_act) {
            $act_service = new MedicalActService();
            if (
                !CJfseAct::actExists($medical_act->getId(), $data_model->_id)
                || ($medical_act->getId() == '' && in_array($medical_act->getActCode(), MedicalAct::$complement_list))
            ) {
                $act_service->createCActeFromMedicalAct($medical_act, $invoice);
            } else {
                $act_service->updateCActeFromMedicalAct($medical_act, $invoice);
            }
        }
    }

    public static function consultationHasInvoices(CConsultation $consultation, bool $cache = true): bool
    {
        return boolval($consultation->countBackRefs('jfse_invoices', [], [], $cache));
    }

    public static function consultationHasPendingInvoices(CConsultation $consultation): bool
    {
        return boolval($consultation->countBackRefs('jfse_invoices', ['status' => " = 'pending'"]));
    }

    public static function consultationHasValidatedInvoices(CConsultation $consultation): bool
    {
        return boolval($consultation->countBackRefs('jfse_invoices', ['status' => " = 'validated'"]));
    }

    public static function getFirstPendingInvoiceFromConsultation(CConsultation $consultation): CJfseInvoice
    {
        return self::getFirstInvoiceWithStatusFromConsultation($consultation, 'pending');
    }

    public static function getFirstValidatedInvoiceFromConsultation(CConsultation $consultation): CJfseInvoice
    {
        return self::getFirstInvoiceWithoutStatusFromConsultation($consultation, 'pending');
    }

    protected static function getFirstInvoiceWithStatusFromConsultation(
        CConsultation $consultation,
        string $status
    ): CJfseInvoice {
        unset($consultation->_count['jfse_invoices']);
        unset($consultation->_back['jfse_invoices']);

        $invoice = new CJfseInvoice();
        $invoices = $consultation->loadBackRefs(
            'jfse_invoices',
            null,
            null,
            null,
            null,
            null,
            '',
            ['status' => " = '$status'"]
        );

        if (is_array($invoices) && count($invoices)) {
            $invoice = reset($invoices);
        }

        return $invoice;
    }

    protected static function getFirstInvoiceWithoutStatusFromConsultation(
        CConsultation $consultation,
        string $status
    ): CJfseInvoice {
        unset($consultation->_count['jfse_invoices']);
        unset($consultation->_back['jfse_invoices']);
        $invoice = new CJfseInvoice();
        $invoices = $consultation->loadBackRefs(
            'jfse_invoices',
            null,
            null,
            null,
            null,
            null,
            '',
            ['status' => " != '$status'"]
        );

        if (is_array($invoices) && count($invoices)) {
            $invoice = reset($invoices);
        }

        return $invoice;
    }

    public static function getAllInvoicesFromConsultation(CConsultation $consultation): array
    {
        if (array_key_exists('jfse_invoices', $consultation->_back)) {
            unset($consultation->_back['jfse_invoices']);
        }

        $invoices = $consultation->loadBackRefs('jfse_invoices');
        foreach ($invoices as $invoice) {
            $invoice->setLabel();
        }

        return $invoices;
    }

    /**
     * Checks if the invoice can be anonymize
     *
     * @param Invoice $invoice
     */
    protected function checkAnonymize(Invoice $invoice): void
    {
        $beneficiary = $invoice->getBeneficiary();

        $anonymize = false;
        if ($beneficiary && $beneficiary->getPatient() && $invoice->getCreationDate()) {
            $birth_date = $beneficiary->getPatient()->getBirthDate();

            if (substr($birth_date, 5, 2) > 12) {
                $birth_date = substr($birth_date, 0, 5) . '12' . substr($birth_date, 7, 3);
            }

            $durations = CMbDT::achievedDurationsDT(
                $birth_date,
                $invoice->getCreationDate()->format('Y-m-d')
            );

            if (
                $durations['year'] >= 15 && $durations['year'] <= 18
                && $invoice->getConsultation()->loadRefPatient()->sexe == 'f'
            ) {
                $anonymize = true;
            }
        }

        $invoice->getUserInterface()->setAnonymize($anonymize);
    }

    /**
     * Checks if the Invoice uses the ApCV, and if the ApCVContext used is a demonstration context.
     * If it is the case, add a message that inform the user
     *
     * @param Invoice $invoice
     */
    protected function checkApCVDemo(Invoice $invoice): void
    {
        if ($invoice->isApCv()) {
            $card = (new ApCvService())->getVitalCard();
            if ($card && $card->getType() === 'D') {
                $invoice->addMessage(new Message('ApCVDemo', Message::INFO, CAppUI::tr('CApCVContext-msg-demo')));
            }
        }
    }

    /**
     * Checks if the Invoice uses the ApCV, and if the ApCVContext used is a demonstration context.
     * If it is the case, add a message that inform the user
     *
     * @param Invoice $invoice
     */
    public function checkInvoiceApCVDemoMode(Invoice $invoice): void
    {
        if ($invoice->isApCv()) {
            $card = $this->getVitalCard();
            if ($card && $card->getType() === 'D') {
                // add message in the invoice
            }
        }
    }

    private function displayTreatmentType(Invoice $invoice): void
    {
        if ($user = $invoice->getPractitioner()) {
            if (in_array($user->getSituation()->getSpecialityCode(), ['21', '24', '26', '27', '28', '29'])) {
                $invoice->getUserInterface()->setDisplayTreatmentType(true);
            }
        }
    }

    public function getListStsReferralCodes(): array
    {
        $response = $this->client->getListStsReferralCodes();

        return CMbArray::get($response->getContent(), 'lstAiguillagesSTS', []);
    }

    public function getListTreatmentIndicators(): array
    {
        $response_mutuelle = $this->client->getListTreatmentIndicators('M');
        $response_amc = $this->client->getListTreatmentIndicators('A');

        return [
            'mutuelle' => CMbArray::get($response_mutuelle->getContent(), 'lstIndicateursTraitements', []),
            'amc'      => CMbArray::get($response_amc->getContent(), 'lstIndicateursTraitements', [])
        ];
    }

    public function getListAmoServices(string $invoice_id): array
    {
        $response = $this->client->getServiceAmo($invoice_id);

        $services = [];
        foreach (CMbArray::get($response->getContent(), 'lst', []) as $item) {
            $services[] = [
                'code' => CMbArray::get($item, 'code'),
                'label' => CMbArray::get($item, 'libelle'),
            ];
        }

        return $services;
    }

    public function getExonerationList(string $invoice_id): array
    {
        $response = $this->client->getListExonerationCodes($invoice_id);

        $exonerations = [];
        foreach (CMbArray::get($response->getContent(), 'lst', []) as $item) {
            $exonerations[] = [
                'code' => CMbArray::get($item, 'code'),
                'label' => CMbArray::get($item, 'libelle'),
            ];
        }

        return $exonerations;
    }

    public function getSituationsCodesList(CPatient $patient): array
    {
        if ($patient->countBackRefs('jfse_patient')) {
            $beneficiary = VitalCardMapper::getBeneficiaryFromPatientDataModel(
                $patient->loadUniqueBackRef('jfse_patient'),
                true
            );
        } else {
            $beneficiary = VitalCardMapper::getBeneficiaryFromPatient($patient, true);
        }

        $response = $this->client->getListSituationCodes($beneficiary->getInsured()->getRegimeCode());

        $situations = [];
        foreach (CMbArray::get($response->getContent(), 'lst', []) as $item) {
            $situations[] = [
                'code'          => CMbArray::get($item, 'code'),
                'label'         => CMbArray::get($item, 'libelle'),
                'standard_rate' => CMbArray::get($item, 'tauxStd'),
                'ald_rate'      => CMbArray::get($item, 'tauxAld'),
            ];
        }

        return $situations;
    }

    /**
     * Checks that all the requirements for creating an Invoice are met, such as the existence of the patient in Jfse
     *
     * @param CConsultation $consultation
     *
     * @return bool
     */
    public static function checkInvoiceRequirements(CConsultation $consultation): bool
    {
        return CJfsePatient::isPatientLinked($consultation->loadRefPatient());
    }

    /**
     * Checks that all the mandatory data for initializing an invoice without a VitalCard is set for the given patient
     *
     * @param CPatient $patient
     *
     * @return bool
     */
    public function hasMandatoryDataForCardlessModes(CPatient $patient): bool
    {
        $result = false;
        if (CJfsePatient::isPatientLinked($patient)) {
            $result = true;
        } elseif (
            $patient->naissance && $patient->code_regime && $patient->matricule
            && $patient->rang_naissance && $patient->code_gestion
        ) {
            $result = true;
        }

        return $result;
    }

    public function handleValidateInvoiceWarnings(Response $response): void
    {
        if ($response->hasMessages()) {
            $messages_to_handle = [];

            foreach ($response->getMessages() as $message) {
                if (
                    $message->getLevel() !== Message::INFO && $message->getSource() !== 107
                    && $message->getSourceLibrary() !== 'CREER FACTURE' && $message->getTypeId() !== 'M223'
                ) {
                    $messages_to_handle[] = $message;
                }
            }

            if (count($messages_to_handle)) {
                throw new ApiMessageException($messages_to_handle);
            }
        }
    }
}
