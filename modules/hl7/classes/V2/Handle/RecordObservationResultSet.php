<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use DOMNode;
use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Erp\CabinetSIH\CCabinetSIHRecordData;
use Ox\Interop\Eai\Repository\PatientRepository;
use Ox\Interop\Eai\Repository\SejourRepository;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionError;
use Ox\Interop\Hl7\V2\Handle\ObservationResultSet\HandleORSObservation;
use Ox\Interop\Hl7\V2\Handle\ObservationResultSet\HandleORSObservationLabo;
use Ox\Interop\Hl7\V2\Handle\ObservationResultSet\HandleORSObservationMDM;
use Ox\Interop\Hl7\V2\Handle\ObservationResultSet\HandleORSObservationORU;
use Ox\Interop\Hl7\V2\Handle\ObservationResultSet\HandleORSObservationPerop;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHL7v2RecordObservationResultSet
 * Record observation result set, message XML
 */
class RecordObservationResultSet extends CHL7v2MessageXML
{
    /** @var string[] */
    public $codes = [];
    /** @var CHL7v2Acknowledgment */
    public $_ref_ack;
    /** @var array */
    public $_ref_data;
    /** @var int */
    protected $count_treated;
    /** @var CPatient|null */
    protected $patient;
    /** @var CSejour */
    protected $sejour;
    /** @var bool */
    protected $is_mode_sas;
    /** @var string */
    protected $patientPI;
    /** @var string */
    protected $venueRI;
    /** @var string */
    public $venueAN;

    /** @var string[] */
    private $patientINS_NIR;

    /** @var string[] */
    private $patientINS_NIA;

    /** @var string */
    private $patientRI;

    /**
     * CHL7v2RecordObservationResultSet constructor.
     *
     * @param string $encoding
     */
    public function __construct($encoding = "utf-8")
    {
        parent::__construct($encoding);

        $this->count_treated = 0;
    }

    /**
     * Handle event
     *
     * @param CHL7v2Acknowledgment $ack     Acknowledgement
     * @param CMbObject|CPatient   $patient Person
     * @param array                $data    Nodes data
     *
     * @return null|string
     * @throws CHL7v2Exception
     * @throws Exception
     */
    public function handle(CHL7Acknowledgment $ack = null, CMbObject $patient = null, $data = [])
    {
        // Traitement du message des erreurs
        $comment         = "";
        $object          = null;
        $this->_ref_ack  = $ack;
        $this->_ref_data = $data;

        // initialize handle
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $exchange_hl7v2->_ref_sender->loadConfigValues();
        $sender = $this->_ref_sender = $exchange_hl7v2->_ref_sender;

        $this->is_mode_sas = $sender->_configs['mode_sas'];

        $this->patientINS_NIR = CMbArray::pluck(CMbArray::getRecursive($data, "personIdentifiers INS-NIR", []), 'id_number');
        $this->patientINS_NIA = CMbArray::pluck(CMbArray::getRecursive($data, "personIdentifiers INS-NIA", []), 'id_number');
        $this->patientPI   = CMbArray::getRecursive($data, 'personIdentifiers PI');
        $this->patientRI   = CMbArray::getRecursive($data, 'personIdentifiers RI');
        $this->venueRI     = CMbArray::getRecursive($data, 'admitIdentifiers RI');
        $this->venueAN     = $this->getVenueAN($this->_ref_sender, $data);

        try {
            // Pas d'observations
            if (!$first_result = reset($data["observations"])) {
                throw CHL7v2ExceptionError::ackAR($exchange_hl7v2, $ack, "E225", $patient);
            }

            // Management by AppFine
            if ($this->isHandleAppFine()) {
                return CAppFineServer::handleObservationResult($ack, $data, $sender, $patient, $exchange_hl7v2);
            }

            // Management by AppFineClient
            if ($this->isHandleAppFineClient()) {
                return CAppFineClient::handleObservationResult($ack, $data, $sender, $patient, $exchange_hl7v2);
            }

            // Management by Tamm - SIH
            if ($this->isHandleTammSIH()) {
                return CCabinetSIHRecordData::handleRecordObservationResultSet($ack, $data, $sender, $exchange_hl7v2);
            }

            // Search elements (sejour & patient)
            $this->determineContextElements();

            // Treat each elements of observations
            $this->handleObservations($data);

            // Error : no integrated elements
            if ($this->count_treated === 0) {
                throw CHL7v2ExceptionError::ackAR($this->_ref_exchange_hl7v2, $ack, 'E226', $this->patient);
            }
        } catch (CHL7v2ExceptionError $exception) {
            if ($error_ack = $exception->getAck($this->codes)) {
                return $error_ack;
            }

            throw $exception;
        }

        // Success (possibly warning in $this->codes)
        return $exchange_hl7v2->setAckCA($ack, $this->codes, $comment, $object);
    }

    protected function handleObservations(array $data): void
    {
    }

    /**
     * @return bool
     */
    private function isHandleAppFine(): bool
    {
        $is_config_af = (bool)$this->_ref_sender->_configs['handle_portail_patient'];
        $is_appFine   = CModule::getActive("appFine");

        return $is_appFine && $is_config_af;
    }

    /**
     * @return bool
     */
    private function isHandleAppFineClient(): bool
    {
        $is_config_af      = (bool)$this->_ref_sender->_configs['handle_portail_patient'];
        $is_appFine_client = CModule::getActive("appFineClient");

        return $is_appFine_client && $is_config_af;
    }

    /**
     * @return bool
     */
    private function isHandleTammSIH(): bool
    {
        $is_config_af = (bool)CMbArray::get($this->_ref_sender->_configs, "handle_tamm_sih");
        $is_tamm_sih  = CModule::getActive("oxCabinetSIH");

        return $is_tamm_sih && $is_config_af;
    }

    /**
     * Search to determine context elements (sejour & patient)
     *
     * @throws CHL7v2Exception
     */
    protected function determineContextElements(): void
    {
        $this->patient = $this->determinePatient();
        $this->sejour  = $this->sejour && $this->sejour->_id ? $this->sejour : $this->determineSejour();
    }

    /**
     * @return CPatient|null
     * @throws CHL7v2Exception
     * @throws Exception
     */
    protected function determinePatient(): ?CPatient
    {
        $sender   = $this->_ref_sender;

        $ack            = $this->_ref_ack;
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $patient        = new CPatient();
        $this->getPID($this->_ref_data["PID"], $patient);

        $patient = (new PatientRepository($sender->_configs['search_patient_strategy']))
            ->withINS($this->patientINS_NIR, $this->patientINS_NIA)
            ->withIPP($this->patientPI, $sender->_tag_patient)
            ->withPatientSearched($patient, $sender->group_id)
            ->withResourceId($this->patientRI)
            ->find();

        // Find || Create && Edit Patient
        if (!$patient && $sender->_configs['handle_patient_ORU']) {
            $hl7v2_record_person                      = new RecordPerson();
            $hl7v2_record_person->_ref_exchange_hl7v2 = $exchange_hl7v2;
            $msg_ack                                  = $hl7v2_record_person->handle($ack, $patient, $this->_ref_data);

            // Retour de l'acquittement si erreur sur le traitement du patient
            if ($exchange_hl7v2->statut_acquittement == "AR") {
                throw CHL7v2ExceptionError::setAckAR($msg_ack);
            }

            return $patient;
        }

        if ((!$patient || !$patient->_id) && !$this->is_mode_sas) {
            throw CHL7v2ExceptionError::ackAR($exchange_hl7v2, $ack, "E219", $patient);
        }

        return $patient;
    }

    /**
     * Get PID segment
     *
     * @param DOMNode  $node       Node
     * @param CPatient $newPatient Person
     * @param array    $data       Datas
     *
     * @return void
     */
    public function getPID(DOMNode $node, CPatient $newPatient, $data = null)
    {
        $PID5 = $this->query("PID.5", $node);
        foreach ($PID5 as $_PID5) {
            // Nom(s)
            $this->getNames($_PID5, $newPatient, $PID5);

            // Prenom(s)
            $this->getFirstNames($_PID5, $newPatient);
        }

        // Date de naissance
        $PID_7 = $this->queryTextNode("PID.7/TS.1", $node);
        $newPatient->naissance = $PID_7 ? CMbDT::date($PID_7) : null;

        // Sexe du patient
        $newPatient->sexe = CHL7v2TableEntry::mapFrom("1", $this->queryTextNode("PID.8", $node));
    }

    /**
     * @return CSejour|null
     *
     * @throws CHL7v2Exception
     */
    protected function determineSejour(): ?CSejour
    {
        if (!$this->venueAN) {
            return null;
        }

        $sender         = $this->_ref_sender;
        $control_nda    = $sender->_configs["control_nda_target_document"];
        $exchange_hl7v2 = $this->_ref_exchange_hl7v2;
        $ack            = $this->_ref_ack;
        $patient        = $this->patient;

        $sejour = (new SejourRepository(SejourRepository::STRATEGY_ONLY_NDA, SejourRepository::STRATEGY_CURRENT_SEJOUR))
            ->setPatient($patient)
            ->setNDA($this->venueAN, $sender->_tag_sejour)
            ->setGroupId($sender->group_id)
            ->find();

        if ($control_nda) {
            $nda = null;
            if ($sejour) {
                $sejour->loadNDA();
                $nda = $sejour->_NDA;
            }

            if (!$nda || $nda !== $this->venueAN) {
                throw CHL7v2ExceptionError::ackAR($exchange_hl7v2, $ack, "E205", $patient);
            }
        }

        if (!$this->patient && $sejour) {
            $this->patient = $sejour->loadRefPatient();

            // todo check similar patient
        }

        return $sejour;
    }

    /**
     * @return HandleORSObservation
     * @throws CHL7v2ExceptionError
     */
    protected function getObjectObservationHandle(): HandleORSObservation
    {
        // dispatch in function of profile
        switch ($this->_ref_exchange_hl7v2->type) {
            case 'ILW_FRA':
                return new HandleORSObservationLabo($this);

            case 'DEC':
                return new HandleORSObservationPerop($this);

            case 'SINR':
            case 'ORU':
                return new HandleORSObservationORU($this);

            case 'OUL':
                //return new HandleORSObservationOUL($this);

            case 'MDM':
            case 'DRPT':
                return new HandleORSObservationMDM($this);

            default:
                throw CHL7v2ExceptionError::ackAR($this->_ref_exchange_hl7v2, $this->_ref_ack, 'E011', $this->patient);
        }
    }

    public function addElementTreated(): void
    {
        $this->count_treated += 1;
    }

    public function addCode(string $code): void
    {
        $this->codes[] = $code;
    }

    /**
     * @param array $codes
     */
    public function setCodes(array $codes): void
    {
        $this->codes = $codes;
    }

    /**
     * @return string|null
     */
    public function getPatientPI(): ?string
    {
        return $this->patientPI;
    }

    /**
     * @return string
     */
    public function getVenueRI(): ?string
    {
        return $this->venueRI;
    }

    /**
     * @return CSejour
     */
    public function getSejour(): ?CSejour
    {
        return $this->sejour;
    }

    /**
     * @return CPatient
     */
    public function getPatient(): ?CPatient
    {
        return $this->patient;
    }
}
