<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use DOMNode;
use DOMNodeList;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Interop\Hprimsante\Exceptions\CHPrimSanteExceptionError;
use Ox\Interop\Hprimsante\Exceptions\CHPrimSanteExceptionWarning;
use Ox\Interop\Hprimsante\Handle\ORU\Handle;
use Ox\Interop\Hprimsante\Handle\ORU\HandleObservation;
use Ox\Interop\Hprimsante\Handle\ORU\HandleObservationLabo;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHPrimSanteRecordFiles
 * Record Result, message XML
 */
class CHPrimSanteRecordORU extends CHPrimSanteMessageXML
{
    /** @var string */
    public const KEY_P_NODE = 'P_node';
    /** @var string */
    public const KEY_OBX_LIST = 'OBX_nodes';
    /** @var string */
    public const KEY_OBR_LIST = 'OBR_nodes';
    /** @var string */
    public const KEY_OBR_NODE = 'OBR_node';

    /** @var CPatient */
    public $patient;

    /** @var CSejour */
    public $sejour;
    /** @var CStoredObject */
    public $target;

    /** @var CObservationResult[] */
    private $result_treated = [];

    /** @var CFile[] */
    private $file_treated = [];

    /** @var array CHPrimSanteError[] */
    private $errors = [];

    /** @var array */
    public $identifier_sejour;

    /**
     * @see parent::getContentNodes
     */
    public function getContentNodes(): array
    {
        $data                 = [];
        $patient_result_nodes = $this->queryNodes(
            "//ORU.PATIENT_RESULT",
            null,
            $not_used,
            true
        );
        foreach ($patient_result_nodes as $patient_result_node) {
            $patient_result = [self::KEY_P_NODE => $this->queryNode('P', $patient_result_node)];

            $observation_nodes = $this->queryNodes("ORU.ORDER_OBSERVATION", $patient_result_node);
            foreach ($observation_nodes as $observation_node) {
                $patient_result[self::KEY_OBR_LIST][] = [
                    self::KEY_OBR_NODE => $this->queryNode('OBR', $observation_node),
                    self::KEY_OBX_LIST => $this->queryNodes('ORU.OBSERVATION/OBX', $observation_node),
                ];
            }

            $data[] = $patient_result;
        }

        return ['content' => $data];
    }

    /**
     * @inheritdoc
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function handle(CHPrimSanteAcknowledgment $ack, CMbObject $object, $data)
    {
        if (!$data) {
            return "";
        }

        $exchange_hpr = $this->_ref_exchange_hpr;
        $this->errors = [];
        foreach ($data["content"] ?? [] as $index_line => $patient_data) {
            // reset integration for next data of other patient
            $this->loop           = $index_line;
            $this->result_treated = [];
            $this->file_treated   = [];

            /** @var DOMNode $patient_node */
            $patient_node = $patient_data[self::KEY_P_NODE];
            try {
                // try to determine patient
                $this->patient = $this->determinePatient($patient_node);

                if ($this->patient) {
                    // try to determine sejour
                    $this->sejour = $this->determineSejour($patient_node);
                }

                // treatment observation
                $this->treatObservations($patient_data);
            } catch (CHPrimSanteExceptionError | CHPrimSanteExceptionWarning $error) {
                $this->errors[] = $error->getHprimError($exchange_hpr);
            }
        }

        return $exchange_hpr->setAck($ack, $this->errors);
    }

    /**
     * Add error object
     *
     * @param CHPrimSanteError $error
     *
     * @return void
     */
    public function addError(CHPrimSanteError $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Get object which handle message
     *
     * @return HandleObservation
     */
    protected function getObjectObservationHandle(): HandleObservation
    {
        $handle_type = $this->_ref_sender->_configs['handle_oru_type'];

        // handle ox-labo
        if ($handle_type === 'labo') {
            return new HandleObservationLabo($this);
        }

        // handle files
        return new HandleObservation($this);
    }

    /**
     * Get the mediboard file type
     *
     * @param String $file_type Type file
     *
     * @return null|string
     */
    function getFileType($file_type)
    {
        switch ($file_type) {
            case "PDF":
                $result = "application/pdf";
                break;
            default:
                $result = null;
        }

        return $result;
    }

    /**
     * Get observation date time
     *
     * @param DOMNode $node DOM node
     *
     * @return string
     */
    public function getOBRObservationDateTime(DOMNode $node)
    {
        return $this->queryTextNode("OBR/OBR.7", $node);
    }

    /**
     * Return the author of the document
     *
     * @param DOMNode $node node
     *
     * @return CMediusers|int|null
     */
    function getObservationAuthor(DOMNode $node)
    {
        $OBR_32 = $this->queryNode("OBR/OBR.32", $node);

        return $this->getDoctor($OBR_32, true);
    }

    /**
     * Get the observation type
     *
     * @param DOMNode $observation Observation
     *
     * @return string
     */
    function getObservationType(DOMNode $observation)
    {
        $xpath = new CHPrimSanteMessageXPath($observation ? $observation->ownerDocument : $this);

        return $xpath->queryTextNode("OBX/OBX.2/CE.1", $observation);
    }

    /**
     * Get the observation result
     *
     * @param DOMNode $observation Observation
     *
     * @return string
     */
    function getObservationResult(DOMNode $observation)
    {
        $xpath = new CHPrimSanteMessageXPath($observation ? $observation->ownerDocument : $this);

        return $xpath->queryTextNode("OBX/OBX.5", $observation);
    }

    /**
     * Get observation date time
     *
     * @param DOMNode $node DOM node
     *
     * @return string
     */
    function getOBXObservationDateTime(DOMNode $node)
    {
        return $this->queryTextNode("OBX/OBX.14/TS.1", $node);
    }

    /**
     * @param array $patient_data
     *
     * @return void
     * @throws CHPrimSanteExceptionWarning
     */
    protected function treatObservations(array $patient_data): void
    {
        // ORDER OBSERVATION
        /** @var DOMNodeList $order_observation_list */
        $order_observation_list = $patient_data[self::KEY_OBR_LIST] ?? [];

        /** @var DOMNode $order_observation */
        foreach ($order_observation_list as $index => $order_data) {
            $params = array_merge(
                $order_data,
                [
                    self::KEY_P_NODE      => $patient_data[self::KEY_P_NODE],
                    Handle::KEY_OBR_NODE  => $order_data[self::KEY_OBR_NODE],
                    Handle::KEY_OBR_INDEX => $index,
                ]
            );

            ($this->getObjectObservationHandle())->handle($params);
        }

        // Error : no integrated elements
        $loop = $this->loop + 1;
        if (count($this->file_treated) === 0 && count($this->result_treated) === 0) {
            throw new CHPrimSanteExceptionError('P', '24', ['OBX', "P[$loop]", ['10.4']]);
        } else {
            $comment = CAppUI::tr(
                'CHprimSanteRecordORU-msg-elements added',
                [count($this->file_treated), count($this->result_treated)]
            );

            $this->addError(
                new CHPrimSanteError(
                    $this->_ref_exchange_hpr,
                    'I',
                    '25',
                    ['P', $loop, $this->identifier_patient],
                    null,
                    $comment
                )
            );
        }
    }

    /**
     * @param DOMNode $patient_data
     *
     * @return CPatient
     * @throws CHPrimSanteExceptionError
     */
    protected function determinePatient(DOMNode $patient_node): ?CPatient
    {
        $this->identifier_patient = $identifier = $this->getPersonIdentifiers($patient_node);
        $ipp = $identifier["identifier"];

        if (!$ipp && $this->isModeSAS()) {
            // Identifiants non transmis
            throw new CHPrimSanteExceptionError("P", "01", ["P", $this->loop + 1, $identifier], "8.3");
        }

        // Récupération du patient par idex/match
        $patient = $this->getPatient($identifier, $patient_node);

        // Create patient if not found
        if (!$patient && $this->_ref_sender->_configs['handle_patient_ORU']) {
            $patient = $this->mapPatientFull($patient_node);
            if ($msg = $patient->store()) {
                throw new CHPrimSanteExceptionError("P", "09", ["P", $this->loop + 1, $identifier], "8.3", $msg);
            }

            $idex_ipp = CIdSante400::getMatch($patient->_class, $this->_ref_sender->_tag_patient, $ipp, $patient->_id);
            if (!$idex_ipp->_id) {
                $idex_ipp->store();
            }
        }

        // Patient non trouvé
        if (!$this->isModeSAS()) {
            if (!$patient || !$patient->_id) {
                throw new CHPrimSanteExceptionError("P", "02", ["P", $this->loop + 1, $identifier], "8.3");
            }
        }

        return $patient && $patient->_id ? $patient : null;
    }

    /**
     * @param DOMNode $patient_node
     *
     * @return CSejour|null
     * @throws Exception
     */
    protected function determineSejour(DOMNode $patient_node): ?CSejour
    {
        // Récupération de l'identifiant du sejour
        $this->identifier_sejour = $this->getSejourIdentifier($patient_node);

        // Récupération du séjour idex/match
        $identifier = $this->identifier_sejour["sejour_identifier"];
        $sejour = $this->getSejour($this->patient, $identifier, $patient_node);

        // Add error only if not in sas mode and target is not a sejour
        $target = $this->_ref_sender->_configs['object_attach_OBX'];
        if ($sejour instanceof CHPrimSanteError && $identifier && !$this->isModeSAS() && $target !== 'CPatient') {
            $this->addError(
                new CHPrimSanteError($this->_ref_exchange_hpr, "P", "03", ["P", $this->loop, $identifier], "8.3")
            );

            return null;
        }

        return ($sejour instanceof CSejour && $sejour->_id) ? $sejour : null;
    }

    /**
     * @return bool
     */
    protected function isModeSAS(): bool
    {
        return (bool) $this->_ref_sender && $this->_ref_sender->_configs['mode_sas'];
    }

    /**
     * @param CObservationResult $observation_result
     *
     * @return void
     */
    public function addResultTreated(CObservationResult $observation_result): void
    {
        $this->result_treated[] = $observation_result;
    }

    /**
     * @param CFile $file
     *
     * @return void
     */
    public function addFileTreated(CFile $file): void
    {
        $this->file_treated[] = $file;
    }
}
