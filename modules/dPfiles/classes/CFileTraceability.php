<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Client\CAppFineClientSas;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Erp\CabinetSIH\CCabinetSIH;
use Ox\Erp\CabinetSIH\CCabinetSIHSas;
use Ox\Interop\Dmp\CDMPSas;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\Sas\CSAS;
use Ox\Interop\SIHCabinet\CReceiverHL7v2SIHCabinet;
use Ox\Interop\SIHCabinet\CSIHCabinet;
use Ox\Interop\SIHCabinet\CSIHCabinetSas;
use Ox\Interop\Sisra\CZepraSas;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * CFileTraceability
 */
class CFileTraceability extends CMbObject
{
    public const INITIATOR_SERVER = 'server';
    public const INITIATOR_CLIENT = 'client';

    /**
     * @var integer Primary key
     */
    public $file_traceability_id;

    public $created_datetime;
    public $modified_datetime;
    public $sent_datetime;
    public $received_datetime;
    public $user_id;
    public $object_id;
    public $object_class;
    public $actor_id;
    public $actor_class;
    public $group_id;
    public $source_name;
    public $status;
    public $treated_datetime;
    public $comment;
    public $motif_invalidation;
    public $attempt_treated;
    public $IPP;
    public $NDA;
    public $NIR;
    public $patient_name;
    public $patient_birthname;
    public $patient_firstname;
    public $patient_date_of_birth;
    public $patient_sexe;
    public $oid_nir;
    public $datetime_object;
    public $praticien_id;
    public $initiator;
    public $metadata;
    public $version;
    public $attempt_sent;
    public $cancel;
    public $exchange_class;
    public $exchange_id;
    public $msg_error;
    public $type_request;
    /** @var string $report */
    public $report;
    public $dissociated;

    public $_status;
    public $_alert_day;
    public $_date_min;
    public $_date_max;
    public $_total_files = 0;

    /** @var CMediusers */
    public $_ref_user;
    /** @var CFile */
    public $_ref_file;
    /** @var CInteropActor */
    public $_ref_actor;
    /** @var CDocumentItem */
    public $_ref_object;
    /** @var CPatient */
    public $_ref_patient;
    /** @var CSejour */
    public $_ref_sejour;

    /** @var string */
    public $_visibilite;

    /**
     * Purge the CFile and CFileTraceability with the status 'Archived' after x days
     *
     * @return bool|resource
     */
    static function purgeProbably()
    {
        if (!CAppUI::gconf('sas CFile launch_purge_files')) {
            return;
        }

        $nbr_day = (int)CAppUI::gconf('sas CFile launch_purge_files');
        $limit   = 100;

        $now      = CMbDT::dateTime();
        $to_purge = CMbDT::date("- {$nbr_day} DAY", $now) . ' 00:00:00';

        $file_traceability = new self();
        $ds                = $file_traceability->getDS();

        $where           = ['created_datetime' => $ds->prepare('<= ?', $to_purge)];
        $where['status'] = " = 'archived'";

        $request = new CRequest();
        $request->addTable($file_traceability->_spec->table);
        $request->addWhere($where);
        $request->setLimit($limit);

        return $ds->exec($request->makeDelete());
    }

    static function generateExchange(CFileTraceability $file_traceability)
    {
        $document = $file_traceability->loadRefObject();
        // Cas d'un document non finalisé => on ne l'envoie pas
        if (!$document->send) {
            $file_traceability->sent_datetime = "now";
            $file_traceability->setMsgError("CFileTraceability-msg-Doc not finished");
        }

        $receiver = $file_traceability->loadRefActor();
        if (!$receiver->actif) {
            return;
        }

        switch ($receiver->_class) {
            case "CReceiverHL7v2":
                /** @var CReceiverHL7v2 */
                $receiver->loadConfigValues();

                if (
                    CModule::getActive("appFineClient") && $receiver->_configs['send_evenement_to_mbdmp']
                ) {
                    $target = $document->loadTargetObject();
                    if ($target instanceof CPrescription) {
                        $target = $target->loadRefObject();
                    }
                    if (!CAppFineClient::loadIdex($target)->_id) {
                        return;
                    }

                    if (!$sas = CSAS::generator("appFineClient")) {
                        return;
                    }
                    $sas::generateExchange($file_traceability);
                }

                if (CModule::getActive("oxSIHCabinet") && $receiver->_configs['sih_cabinet_id']
                    && CSIHCabinet::loadIdex($document->loadTargetObject())->_id
                ) {
                    if (!$sas = CSAS::generator("SIHCabinet")) {
                        return;
                    }
                    $sas::generateExchange($file_traceability);
                }

                if (CModule::getActive("oxCabinetSIH") && $receiver->_configs['cabinet_sih_id']
                    && CCabinetSIH::loadIdex($document->loadTargetObject())->_id
                ) {
                    if (!$sas = CSAS::generator("CabinetSIH")) {
                        return;
                    }
                    $sas::generateExchange($file_traceability);
                }

                return;

            case "CReceiverHL7v3":
                if (!$sas = CSAS::generator($receiver->type)) {
                    return;
                }

                $sas::generateExchange($file_traceability);

                return;

            default;
        }
    }

    /**
     * Load Ref Object
     *
     * @return CDocumentItem|void
     * @throws Exception
     */
    function loadRefObject()
    {
        if (!$this->object_class) {
            return;
        }
        $this->_ref_object = new $this->object_class;
        $this->_ref_object->load($this->object_id);

        return $this->_ref_object;
    }

    /**
     * Put error on object
     *
     * @param string $msg error message
     *
     * @return void
     * @throws Exception
     */
    function setMsgError($msg)
    {
        $this->status    = "rejected";
        $this->msg_error = CAppUI::tr($msg);

        return $this->store();
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        // Check some purge when creating a CFileTraceability
        if (!$this->_id) {
            CApp::doProbably(CAppUI::gconf('sas CFile launch_purge_files'), [$this, 'purgeProbably']);
            CApp::doProbably(CAppUI::gconf('sas CFile retention_received_files'), [$this, 'moveFilesIntoArchived']);

            $this->created_datetime = $this->modified_datetime = "now";

            $actor          = $this->loadRefActor();
            $this->group_id = $actor->group_id;
        }

        $docItem       = $this->loadRefObject();
        $this->version = ($docItem && $docItem->_id) ? $docItem->_version : 1;

        if ($this->_id && $this->status !== 'pending') {
            $this->dissociated = 0;
        }

        // On modifie la date uniquement si la traçabilité a été modifiée
        if ($this->objectModified()) {
            $this->modified_datetime = "now";
        }

        if ($msg = parent::store()) {
            return $msg;
        }
    }

    /**
     * Load interop actor
     *
     * @return CInteropActor|CStoredObject
     */
    function loadRefActor()
    {
        return $this->_ref_actor = $this->loadFwdRef("actor_id", true);
    }

    /**
     * Create file traceability from doc item and receiver
     *
     * @param CDocumentItem    $documentItem    document item
     * @param CInteropReceiver $receiver        receiver
     * @param array            $metadata        metadata
     * @param string           $action_file_dmp action file
     *
     * @return string|bool
     * @throws Exception
     */
    static function createTrace(
        CDocumentItem    $documentItem,
        CInteropReceiver $receiver,
                         $manuel = false,
                         $metadata = null,
                         $action_file_dmp = null
    ) {
        if ($receiver instanceof CReceiverHL7v2SIHCabinet) {
            // Permet de stocker en BDD CReceiverHL7v2
            $receiver->_class = CClassMap::getSN(CReceiverHL7v2::class);
        }

        $file_traceability = new CFileTraceability();
        $file_traceability->setObject($documentItem);
        $file_traceability->setActor($receiver);

        if (!self::doShare($receiver, $documentItem, $manuel, $action_file_dmp)) {
            return false;
        }

        $source_name = CFileTraceability::getSourceName($receiver, $documentItem);
        if (!$source_name) {
            return false;
        }
        $file_traceability->group_id    = $receiver->group_id;
        $file_traceability->source_name = $source_name;
        $file_traceability->status      = "pending";
        $file_traceability->initiator   = CFileTraceability::INITIATOR_CLIENT;
        $type_request                   = CFileTraceability::findRequestType(
            $documentItem,
            $receiver,
            $action_file_dmp
        );
        if ($type_request) {
            $file_traceability->type_request = $type_request;
        }
        $file_traceability->loadMatchingObject();
        $file_traceability->user_id = CAppUI::$instance->user_id;
        // Reset nb tentatives au cas ou on retrouve une trace avec le loadMatchingObject
        $file_traceability->attempt_sent = 0;
        $file_traceability->cancel       = 0;

        if ($metadata !== null) {
            $file_traceability->metadata = $metadata;
        }

        $create = $file_traceability->_id ? false : true;

        if ($msg = $file_traceability->store()) {
            return $msg;
        }

        return $create ? "CFileTraceability-msg-create" : "CFileTraceability-msg-modify";
    }

    /**
     * Set object
     *
     * @param CMbObject $object Object
     *
     * @return void
     */
    function setObject(CMbObject $object)
    {
        $this->object_class = $object->_class;
        $this->object_id    = $object->_id;
    }

    /**
     * Set actor on CDocumentManifest
     *
     * @param CInteropActor $actor actor
     *
     * @return void
     */
    function setActor(CInteropActor $actor)
    {
        $this->actor_class = $actor->_class;
        $this->actor_id    = $actor->_id;
    }

    /**
     * Check if it's possible to share doc with this receiver
     *
     * @param CInteropReceiver $receiver     Receiver
     * @param CDocumentItem    $documentItem Document
     *
     * @return bool
     * @throws Exception
     */
    static function doShare(
        CInteropReceiver $receiver,
        CDocumentItem    $documentItem,
                         $manual_mode = false,
                         $action_file_dmp = null
    ) {
        switch ($receiver->_class) {
            case "CReceiverHL7v2":
                $receiver->loadConfigValues();

                if (CModule::getActive("appFineClient") && $receiver->_configs['send_evenement_to_mbdmp']) {
                    return CAppFineClientSas::doShareAppFine($receiver, $documentItem);
                }
                if (
                    CModule::getActive("oxSIHCabinet") && CSIHCabinet::loadIdex($documentItem->loadTargetObject())->_id
                ) {
                    return CSIHCabinetSas::doShareSIHCabinet($receiver, $documentItem);
                }

                if (
                    CModule::getActive("oxCabinetSIH") && CCabinetSIH::loadIdex($documentItem->loadTargetObject())->_id
                ) {
                    return CCabinetSIHSas::doShareCabinetSIH($receiver, $documentItem);
                }

                return false;

            case "CReceiverHL7v3":
                /** @var CReceiverHL7v3 $receiver */
                switch ($receiver->type) {
                    case "DMP":
                        return CDMPSas::doShareDMP($receiver, $documentItem, $manual_mode, $action_file_dmp);
                    case "ZEPRA":
                        return CZepraSas::doShareSisra($receiver, $documentItem);
                    default:
                        return false;
                }

            default:
                return false;
        }
    }

    /**
     * Get source name
     *
     * @param CInteropReceiver $receiver Receiver
     * @param CDocumentItem    $docItem  Doc item
     *
     * @return null|string
     */
    static function getSourceName(CInteropActor $actor, ?CDocumentItem $docItem = null)
    {
        $actor->loadConfigValues();

        if ($actor instanceof CInteropReceiver) {
            switch ($actor->_class) {
                case 'CReceiverHL7v2':
                    if (
                        CModule::getActive("appFineClient") && CMbArray::get(
                            $actor->_configs,
                            'send_evenement_to_mbdmp'
                        )
                    ) {
                        $target = $docItem->loadTargetObject();
                        // Cas des ordonnances
                        if ($target instanceof CPrescription) {
                            $target = $target->loadRefObject();
                        }

                        if (CAppFineClient::loadIdex($target)->_id) {
                            return CAppFineClientSas::getTag();
                        }
                    }
                    if (
                        CModule::getActive("oxSIHCabinet") && CSIHCabinet::loadIdex($docItem->loadTargetObject())->_id
                    ) {
                        return CSIHCabinetSas::getTag();
                    }
                    if (
                        CModule::getActive("oxCabinetSIH") && CCabinetSIH::loadIdex($docItem->loadTargetObject())->_id
                    ) {
                        return CCabinetSIHSas::getTag();
                    }

                    break;

                case 'CReceiverHL7v3':
                    /** @var CReceiverHL7v3 $actor */
                    switch ($actor->type) {
                        case CInteropActor::ACTOR_DMP:
                            return CDMPSas::getTag();

                        case CInteropActor::ACTOR_ZEPRA:
                            return CZepraSas::getTag();

                        default:
                    }
                    break;

                default:
            }
        }

        if ($actor instanceof CInteropSender) {
            switch ($actor->type) {
                case CInteropActor::ACTOR_TAMM:
                    return CSIHCabinetSas::getTag();

                case CInteropActor::ACTOR_ZEPRA:
                    return CZepraSas::getTag();

                default:
            }
        }

        return $actor->libelle ?: $actor->nom;
    }

    /**
     * Find request type from document item and receiver
     *
     * @param CDocumentItem    $documentItem    document item
     * @param CInteropReceiver $receiver        receiver
     * @param string           $action_file_dmp action file
     *
     * @return null|string
     * @throws Exception
     */
    static function findRequestType(CDocumentItem $documentItem, CInteropReceiver $receiver, $action_file_dmp = null)
    {
        switch ($receiver->_class) {
            case "CReceiverHL7v2":
                $receiver->loadConfigValues();
                if (CModule::getActive("appFineClient") && $receiver->_configs['send_evenement_to_mbdmp']
                    && CAppFineClient::loadIdex($documentItem->loadTargetObject())->_id
                ) {
                    return "add";
                }

                return null;

            case "CReceiverHL7v3":
                /** @var CReceiverHL7v3 $receiver */
                switch ($receiver->type) {
                    case "DMP":
                        $documentItem->checkSynchroDMP($receiver);
                        if ($action_file_dmp) {
                            return "delete";
                        }

                        switch ($documentItem->_status_dmp) {
                            case "0":
                            case "5":
                                return "add";
                            case "1":
                                return "modify";
                            case "2":
                                return "replace";
                            default:
                                return null;
                        }

                    case "ZEPRA":
                        return "add";

                    default:
                        return null;
                }
                break;

            default;
                return null;
        }
    }

    public static function countFilesUnread(): int
    {
        $ljoin               = [];
        $ljoin["files_read"] = "file_traceability.object_id = files_read.object_id AND file_traceability.object_class = files_read.object_class";

        $where                                = [];
        $where["files_read.file_read_id"]     = "IS NULL";
        $where["file_traceability.group_id"]  = " = '" . CGroups::loadCurrent()->_id . "'";
        $where["file_traceability.initiator"] = " = 'server'";

        $file_traceability = new CFileTraceability();

        return $file_traceability->countList($where, null, $ljoin);
    }

    /**
     * Get the files list.
     *
     * @param array    $where   Optional conditions
     * @param array    $order   Order SQL statement
     * @param array    $limit   Limit SQL statement
     * @param CPatient $patient Patient
     *
     * @return CFileTraceability[]
     * @throws Exception
     */
    function getFiles($where = [], $order = null, $limit = null, CPatient $patient = null, $ljoin = [])
    {
        $ljoin["files_mediboard"] = "files_mediboard.file_id = file_traceability.object_id AND file_traceability.object_class = 'CFile' ";
        $ljoin["compte_rendu"]    =
            "compte_rendu.compte_rendu_id = file_traceability.object_id AND file_traceability.object_class = 'CCompteRendu' ";

        if ($patient) {
            $files_traceabilities = $this->filterPatient($patient, $where, $order, $limit, $ljoin);
        } else {
            $file_traceability    = new self();
            $files_traceabilities = $file_traceability->loadList($where, $order, $limit, null, $ljoin);
            $this->_total_files   = $file_traceability->countList($where, null, $ljoin);
        }

        CStoredObject::massLoadFwdRef($files_traceabilities, "object_id");

        foreach ($files_traceabilities as $_file_traceability) {
            $_file_traceability->loadRefActor();

            $file = $_file_traceability->loadRefObject();
            if (!$file || !$file->_id) {
                unset($files_traceabilities[$_file_traceability->_id]);
                continue;
            }

            $_file_traceability->showAlert();

            $object = $file->loadTargetObject();

            if ($object instanceof CFileTraceability || !$object) {
                $_file_traceability->loadRefPatient();
                $_file_traceability->loadRefSejour();

                continue;
            }

            $sejour = null;
            if ($object instanceof CPatient) {
                $patient = $object;
            } elseif ($object instanceof CSejour) {
                $patient = $object->loadRefPatient();
                $sejour  = $object;
            } elseif ($object instanceof CPrescription) {
                $patient = $object->loadRefPatient();
            } elseif ($object instanceof CObservationResultSet) {
                $patient = $object->loadRefPatient();
            } else {
                /** @var CConsultation|COperation $object */
                $patient = $object->loadRefPatient();
                $sejour  = $object->loadRefSejour();
            }

            $_file_traceability->_ref_patient = $patient;
            if ($sejour) {
                $_file_traceability->_ref_sejour = $sejour;
            }
        }

        return $files_traceabilities;
    }

    /**
     * Filter on the patients
     *
     * @param CPatient $filter_patient Patient
     * @param array    $where          Optional conditions
     * @param array    $order          Order SQL statement
     * @param array    $limit          Limit SQL statement
     * @param array    $ljoin          Optionnals left join parameters
     *
     * @return CFileTraceability[]|null
     */
    function filterPatient(CPatient $filter_patient, $where = [], $order = null, $limit = null, $ljoin = null)
    {
        $file_traceability    = new CFileTraceability();
        $files_traceabilities = $file_traceability->loadList($where, $order, null, null, $ljoin);

        CStoredObject::massLoadFwdRef($files_traceabilities, "object_id");

        foreach ($files_traceabilities as $key => $_file_traceability) {
            $file = $_file_traceability->loadRefObject();
            if (!$file || !$file->_id) {
                unset($files_traceabilities[$_file_traceability->_id]);
                continue;
            }

            $object = $file->loadTargetObject();

            if ($object instanceof CFileTraceability || !$object) {
                $patient = $_file_traceability->loadRefPatient();
            } elseif ($object instanceof CPatient) {
                $patient = $object;
            } elseif ($object instanceof CSejour) {
                $patient = $object->loadRefPatient();
            } else {
                /** @var CConsultation|COperation $object */
                $patient = $object->loadRefPatient();
            }

            // Recherche sur un patient en particulier de la BDD
            if ($filter_patient->_id) {
                // Si on recherche sur un patient_id de MB, on retourne si on n'arrive pas à récupérer le patient sur la trace
                if (!$patient || !$patient->_id) {
                    unset($files_traceabilities[$key]);
                    continue;
                }

                // Check sur le patient (on unset la trace que si le nom du patient ou prénom du patient ou patient_id ne matchent pas)
                if (($filter_patient->nom && strtoupper($patient->nom) != strtoupper($filter_patient->nom))
                    || ($filter_patient->prenom && strtoupper($patient->prenom) != strtoupper($filter_patient->prenom))
                    || ($filter_patient->_id && $patient->_id && $filter_patient->_id != $patient->_id)
                ) {
                    unset($files_traceabilities[$key]);
                    continue;
                }
            }

            // Recherche libre sur nom et/ou prénom (soit on recherche sur la cible de la trace, soit sur le nom/prénom de la trace
            if ($filter_patient->_search_free) {
                if ($filter_patient->nom) {
                    if ((strtoupper($_file_traceability->patient_name) != strtoupper(
                                $filter_patient->nom
                            )) && (strtoupper($patient->nom) != strtoupper($filter_patient->nom))) {
                        unset($files_traceabilities[$key]);
                        continue;
                    }
                }

                if ($filter_patient->prenom) {
                    if (strtoupper($_file_traceability->patient_firstname) != strtoupper(
                            $filter_patient->prenom
                        ) && strtoupper($patient->prenom) != strtoupper($filter_patient->prenom)) {
                        unset($files_traceabilities[$key]);
                        continue;
                    }
                }
            }
        }

        $this->_total_files = count($files_traceabilities);

        return $files_traceabilities;
    }

    /**
     * Load patient
     *
     * @return CPatient
     */
    function loadRefPatient()
    {
        $patient = new CPatient();

        // Search by IPP
        if (CAppUI::gconf('sas search search_by_ipp') && $this->IPP) {
            $patient->_IPP = $this->IPP;
            $patient->loadFromIPP();
        }

        if ($patient->_id) {
            return $this->_ref_patient = $patient;
        }

        $patient->nom             = $this->patient_name;
        $patient->nom_jeune_fille = $this->patient_birthname;
        $patient->prenom          = $this->patient_firstname;
        $patient->naissance       = $this->patient_date_of_birth;
        $patient->loadMatchingPatient();

        switch (CAppUI::gconf('sas search automatic_patient_reconciliation')) {
            case "strict":
                if ($patient->_id) {
                    // Le champ date de naissance ne peut pas être vide, le prénom non plus, seul un des deux noms peut être absent.
                    if (!$this->patient_date_of_birth || !$this->patient_firstname || (!$this->patient_name && !$this->patient_birthname)) {
                        return $this->_ref_patient = new CPatient();
                    }

                    // Trois champs sur 4 doivent être strictement identiques pour un rapprochement automatique.
                    // S'il existe une différence dans l'un des champs, le document est orienté vers le SAS en attente.
                    if (!$this->checkPatientField($this->patient_firstname, $patient->prenom)
                        || !$this->checkPatientField($this->patient_date_of_birth, $patient->naissance)
                        || (!$this->checkPatientField($this->patient_name, $patient->nom) &&
                            !$this->checkPatientField($this->patient_birthname, $patient->nom_jeune_fille))) {
                        return $this->_ref_patient = new CPatient();
                    }
                }

                break;

            default:
        }

        return $this->_ref_patient = $patient;
    }

    /**
     * Check patient field
     *
     * @param string|null $field1 Field 1
     * @param string|null $field2 Field 2
     *
     * @return bool
     */
    function checkPatientField($field1 = null, $field2 = null)
    {
        if (!$field1 && !$field2) {
            return false;
        }

        if (CMbString::lower($field1) != CMbString::lower($field2)) {
            return false;
        }

        return true;
    }

    /**
     * Show alert before x days to move file to status 'archived'
     *
     * @return void
     */
    function showAlert()
    {
        if (!CAppUI::gconf('sas CFile retention_received_files')) {
            return;
        }

        $move_status_file = CAppUI::gconf('sas CFile retention_received_files');
        $alert_day        = CAppUI::gconf('sas CFile show_tag_alert');

        $date       = CMbDT::date("- {$move_status_file} days");
        $date_alert = CMbDT::date("- {$alert_day} days", $date);

        if ($this->status == 'pending' && $this->created_datetime <= "$date 00:00:00") {
            $day_before_archived = CMbDT::daysRelative($date_alert, $this->created_datetime);

            if ($day_before_archived <= $alert_day) {
                $this->_alert_day = $day_before_archived;
            }
        }
    }

    /**
     * Load sejour
     *
     * @return CSejour
     */
    function loadRefSejour()
    {
        $sejour = new CSejour();

        // Search by NDA
        if (CAppUI::gconf('sas search search_by_nda') && $this->NDA) {
            $sejour->loadFromNDA($this->NDA);
        }

        return $this->_ref_sejour = $sejour;
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "file_traceability";
        $spec->key   = "file_traceability_id";

        return $spec;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props = parent::getProps();

        $props['created_datetime']      = "dateTime notNull";
        $props['modified_datetime']     = "dateTime notNull";
        $props['sent_datetime']         = "dateTime";
        $props['received_datetime']     = "dateTime";
        $props['user_id']               = "ref class|CMediusers notNull back|file_traceability";
        $props["object_id"]             = "ref class|CMbObject meta|object_class back|file_traceability";
        $props["object_class"]          = "enum list|CFile|CCompteRendu default|CFile";
        $props["actor_id"]              = "ref notNull class|CInteropActor meta|actor_class back|file_traceability";
        $props["actor_class"]           = "str notNull class maxLength|80";
        $props['group_id']              = "ref class|CGroups notNull back|file_traceability autocomplete|text";
        $props["exchange_id"]           = "ref class|CExchangeDataFormat meta|exchange_class back|exchange";
        $props["exchange_class"]        = "str class maxLength|80";
        $props['source_name']           = "str";
        $props['status']                = "enum notNull list|auto|pending|sas_manually|sas_auto|archived|rejected";
        $props['treated_datetime']      = "dateTime";
        $props['comment']               = "text";
        $props['motif_invalidation']    = "text";
        $props['attempt_treated']       = "bool default|0";
        $props['IPP']                   = "str maxLength|80";
        $props['NDA']                   = "str maxLength|80";
        $props['NIR']                   = "str maxLength|15";
        $props['oid_nir']               = "str maxLength|80";
        $props['patient_name']          = "str";
        $props['patient_birthname']     = "str";
        $props['patient_firstname']     = "str";
        $props['patient_date_of_birth'] = "birthDate";
        $props['patient_sexe']          = 'enum list|i|m|f fieldset|default';
        $props['datetime_object']       = "dateTime";
        $props['praticien_id']          = "ref class|CMediusers back|file_traceabilities";
        $props["initiator"]             = "enum notNull list|client|server default|server";
        $props["metadata"]              = "text";
        $props["version"]               = "num";
        $props["attempt_sent"]          = "num default|0";
        $props["cancel"]                = "bool default|0";
        $props["msg_error"]             = "str";
        $props["type_request"]          = "enum notNull list|add|replace|modify|cancel|delete default|add";
        $props["report"]                = "str";
        $props["dissociated"]           = "bool default|0";

        $props["_date_min"] = "date";
        $props["_date_max"] = "date";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $docItem = $this->loadRefObject();

        $this->_status = ($docItem && $docItem->object_class && $docItem->object_id) ? "linked" : "unlinked";
    }

    function getMasquage()
    {
        if (!$this->metadata) {
            return $this->_visibilite = null;
        }

        $object = json_decode($this->metadata);

        if (!isset($object->masquage)) {
            return $this->_visibilite = null;
        }

        return $this->_visibilite = $object->masquage;
    }

    /**
     * Load user
     *
     * @return CMediusers|CStoredObject
     */
    function loadRefUser()
    {
        return $this->_ref_user = $this->loadFwdRef("user_id", true);
    }

    /*
     * Get object
     *
     * @return CMbObject
     */

    /**
     * Move files in pending with the status 'Archived' after x days
     *
     * @return void
     */
    function moveFilesIntoArchived()
    {
        if (!CAppUI::gconf('sas CFile retention_received_files')) {
            return;
        }

        $move_status_file = CAppUI::gconf('sas CFile retention_received_files');

        $date = CMbDT::date("- {$move_status_file} days");

        $where                     = [];
        $where["created_datetime"] = " <= '$date 00:00:00'";
        $where["status"]           = " = 'pending'";

        $file_traceability    = new self();
        $files_traceabilities = $file_traceability->loadList($where);

        foreach ($files_traceabilities as $_file_traceability) {
            // move status 'archived'
            /** @var CFile $file */
            $_file_traceability->status = 'archived';

            if ($msg = $_file_traceability->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }
        }
    }

    function getObject()
    {
        if (!$this->_ref_object) {
            $this->loadRefObject();
        }
        $file = $this->_ref_object;

        $object_found      = null;
        $object_attach_OBX = null;

        $object = $file->loadTargetObject();

        if ($object && $object->_id && !$object instanceof CFileTraceability) {
            return $object;
        }
        // Si le CFile référence un fichier de traçabilité
        if ($object instanceof CFileTraceability || !$object) {
            $sender = $this->loadRefActor();
            // Cas où la catégorie à un identifiant
            if ($file->_id && $file->file_category_id && $sender->_id) {
                $files_category = new CFilesCategory();
                $files_category->load($file->file_category_id);
                switch ($files_category->class) {
                    case "CPatient":
                    case "CSejour":
                    case "COperation":
                    case "CConsultation":
                        $object_attach_OBX = $files_category->class;
                        break;

                    default:
                        $object_attach_OBX = "CMbObject";
                        break;
                }
            }

            // Search by data
            $patient = $this->loadRefPatient();
            if (!$patient->_id) {
                return null;
            }
            $object_found = $this->searchObject($object_attach_OBX, $patient);

            if (!CAppUI::gconf('sas search advanced_search_patient_duplicate')) {
                return $object_found;
            }

            // Si dans Mediboard il existe un doublon strict de patient sur les traits d'identité la recherche de rattachement du document
            // doit se poursuivre afin de trouver la bonne cible
            $object_found = $this->searchObject($object_attach_OBX, $patient);
            $patient_ids  = $patient->getDoubloonIds();
            if ($patient_ids && !$object_found) {
                foreach ($patient_ids as $_patient_id) {
                    $patient = new CPatient();
                    $patient->load($_patient_id);

                    $object_found = $this->searchObject($object_attach_OBX, $patient);
                    if ($object_found) {
                        return $object_found;
                    }
                }
            }

            return $object_found;
        }
    }

    /**
     * Search object
     *
     * @param string   $object_attach_OBX Object class
     * @param CPatient $patient           Patient
     *
     * @return CConsultation|COperation|CPatient|CSejour|null
     */
    function searchObject($object_attach_OBX, CPatient $patient)
    {
        $object_found = null;
        $date         = $this->datetime_object;
        $praticien_id = $this->praticien_id;

        switch ($object_attach_OBX) {
            // Au patient
            case "CPatient":
                $object_found = $this->loadRefPatient();
                break;

            // Au séjour
            case "CSejour":
                $sejour = $this->loadRefSejour();

                if (!$sejour || !$sejour->_id) {
                    $sejour = $this->searchSejour($date, $patient, $praticien_id);
                }

                $object_found = $sejour;
                break;

            // À l'intervention
            case "COperation":
                $sejour = $this->loadRefSejour();

                $object_found = $this->searchOperation($date, $patient, $praticien_id, $sejour);
                break;

            case "CConsultation":
                $sejour       = $this->loadRefSejour();
                $object_found = $this->searchConsultation($date, $patient, $praticien_id, $sejour);
                break;
            default:
                $sejour = $this->loadRefSejour();

                // Recherche de l'objet avec la date correspondante fourni dans l'observation
                $object_found = $this->getObjectWithDate($date, $patient, $praticien_id, $sejour);
                break;
        }

        return $object_found;
    }

    /**
     * Search admit with document
     *
     * @param String   $dateTime     date
     * @param CPatient $patient      patient
     * @param String   $praticien_id praticien id
     *
     * @return CSejour|null
     */
    function searchSejour($dateTime, CPatient $patient, $praticien_id = null)
    {
        if (!$patient) {
            return null;
        }

        if (!$dateTime) {
            $dateTime = CMbDT::dateTime();
        }

        $group_id = $this->loadRefActor()->group_id;

        $search_min_admit = CAppUI::gconf('sas search_interval search_min_admit');
        $search_max_admit = CAppUI::gconf('sas search_interval search_max_admit');
        $date_before      = CMbDT::date("- $search_min_admit DAY", $dateTime);
        $date_after       = CMbDT::date("+ $search_max_admit DAY", $dateTime);

        if ($praticien_id) {
            $where["praticien_id"] = "= '$praticien_id'";
        }

        $where["patient_id"] = "= '$patient->_id'";
        $where["group_id"]   = "= '$group_id'";
        $where["annule"]     = "= '0'";
        $where["entree"]     = "BETWEEN '$date_before' AND '$date_after'";

        $sejour  = new CSejour();
        $sejours = $sejour->loadList($where);
        if (count($sejours) > 1) {
            return null;
        }

        return reset($sejours);
    }

    /**
     * Search operation with document
     *
     * @param String   $dateTime     date
     * @param CPatient $patient      patient
     * @param String   $praticien_id praticien id
     * @param CSejour  $sejour       sejour
     *
     * @return COperation|null
     */
    function searchOperation($dateTime, CPatient $patient, $praticien_id, $sejour)
    {
        //Recherche de la consutlation dans le séjour
        $date = CMbDT::date($dateTime);

        $search_min_surgery = CAppUI::gconf('sas search_interval search_min_surgery');
        $search_max_surgery = CAppUI::gconf('sas search_interval search_max_surgery');
        $date_before        = CMbDT::date("- $search_min_surgery DAY", $date);
        $date_after         = CMbDT::date("+ $search_max_surgery DAY", $date);

        $group_id = $this->loadRefActor()->group_id;

        $where = [
            "sejour.patient_id"  => "= '$patient->_id'",
            "operations.annulee" => "= '0'",
            "sejour.sejour_id"   => "= '$sejour->_id'",
            "sejour.group_id"    => "= '$group_id'",
        ];

        // Recherche d'une opération dans le séjour
        if ($praticien_id) {
            $where["operations.chir_id"] = "= '$praticien_id'";
        }

        $where[] = "'$dateTime' BETWEEN operations.entree_bloc AND operations.sortie_reveil_reel OR 
      '$dateTime' BETWEEN operations.entree_bloc AND operations.sortie_salle";

        $leftjoin = [
            "sejour" => "operations.sejour_id = sejour.sejour_id",
        ];

        $operation  = new COperation();
        $operations = $operation->loadList($where, null, null, null, $leftjoin);
        if (count($operations) > 1) {
            return null;
        }
        if ($operations) {
            return reset($operations);
        }

        // On recherche avec une période plus large
        if (!$operation->_id) {
            $leftjoin = [
                "sejour"   => "operations.sejour_id = sejour.sejour_id",
                "plagesop" => "operations.plageop_id = plagesop.plageop_id",
            ];

            $where = [
                "sejour.patient_id"  => "= '$patient->_id'",
                "operations.annulee" => "= '0'",
                "sejour.group_id"    => "= '$group_id'",
            ];

            // Recherche d'une opération dans le séjour
            if ($praticien_id) {
                $where[] = "operations.chir_id = '$praticien_id'";
            }

            if ($sejour->_id) {
                $where[] = "sejour.sejour_id = '$sejour->_id'";
            }

            $where[] = "(operations.date BETWEEN '$date_before' AND '$date_after') OR (plagesop.date BETWEEN '$date_before' AND '$date_after')";

            $operation  = new COperation();
            $operations = $operation->loadList($where, "plagesop.date DESC", null, null, $leftjoin);
            if (count($operations) > 1) {
                return null;
            }
            if ($operations) {
                return reset($operations);
            }
        }

        return null;
    }

    /**
     * Search consultation with document
     *
     * @param String   $dateTime     date
     * @param CPatient $patient      patient
     * @param String   $praticien_id praticien id
     * @param CSejour  $sejour       sejour
     *
     * @return CConsultation|null
     */
    function searchConsultation($dateTime, CPatient $patient, $praticien_id, $sejour)
    {
        // Recherche de la consutlation dans le séjour
        $date = CMbDT::date($dateTime);

        $search_min_appointment = CAppUI::gconf('sas search_interval search_min_appointment');
        $search_max_appointment = CAppUI::gconf('sas search_interval search_max_appointment');
        $date_before            = CMbDT::date("- $search_min_appointment DAY", $date);
        $date_after             = CMbDT::date("+ $search_max_appointment DAY", $date);

        $consultation = new CConsultation();
        $where        = [
            "patient_id"        => "= '$patient->_id'",
            "annule"            => "= '0'",
            "plageconsult.date" => "BETWEEN '$date_before' AND '$date_after'",
            "sejour_id"         => "= '$sejour->_id'",
        ];

        // Praticien renseigné dans le message, on recherche par ce dernier
        if ($praticien_id) {
            $where["plageconsult.chir_id"] = "= '$praticien_id'";
        }

        $leftjoin      = ["plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id"];
        $consultations = $consultation->loadList($where, "plageconsult.date DESC", null, null, $leftjoin);
        if (count($consultations) > 1) {
            return null;
        }
        if ($consultations) {
            return reset($consultations);
        }

        //Recherche d'une consultation qui pourrait correspondre
        unset($where["sejour_id"]);
        $consultations = $consultation->loadList($where, "plageconsult.date DESC", null, null, $leftjoin);
        if (count($consultations) > 1) {
            return null;
        }
        if ($consultations) {
            return reset($consultations);
        }

        return null;
    }

    /**
     * Return the object for attach the document
     *
     * @param String   $dateTime     date
     * @param CPatient $patient      patient
     * @param String   $praticien_id praticien id
     * @param CSejour  $sejour       sejour
     *
     * @return CConsultation|COperation|CSejour
     */
    function getObjectWithDate($dateTime, CPatient $patient, $praticien_id, $sejour)
    {
        if ($consultation = $this->searchConsultation($dateTime, $patient, $praticien_id, $sejour)) {
            return $consultation;
        }

        if ($operation = $this->searchOperation($dateTime, $patient, $praticien_id, $sejour)) {
            return $operation;
        }

        if (!$sejour || !$sejour->_id) {
            return $this->searchSejour($dateTime, $patient, $praticien_id);
        }

        return $sejour;
    }

    /**
     * Delete trace
     *
     * @param CDocumentItem $docItem
     * @param string        $type_trace
     *
     * @return void
     * @throws Exception
     */
    public static function deleteTrace(CDocumentItem $docItem, string $source_name = null): void
    {
        $trace = new CFileTraceability();
        $ds    = CSQLDataSource::get('std');
        $where = [
            'status'       => $ds->prepare("= ?", 'pending'),
            'object_id'    => $ds->prepare("= ?", $docItem->_id),
            'object_class' => $ds->prepare("= ?", $docItem->_class),
            'cancel'       => $ds->prepare("= ?", '0'),
        ];

        if ($source_name) {
            $where['source_name'] = $ds->prepare("= ?", $source_name);
        }

        foreach ($trace->loadList($where) as $_trace) {
            $_trace->cancel = 1;
            $_trace->store();
        }
    }


}
