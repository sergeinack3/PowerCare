<?php

/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante\Handle\ORU;

use DOMNode;
use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Eai\Manager\Exceptions\FileManagerException;
use Ox\Interop\Eai\Manager\FileManager;
use Ox\Interop\Eai\Tools\TraceabilityTrait;
use Ox\Interop\Ftp\CSenderFTP;
use Ox\Interop\Ftp\CSenderSFTP;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Interop\Hprimsante\CHPrimSanteRecordORU;
use Ox\Interop\Hprimsante\Exceptions\CHPrimSanteExceptionError;
use Ox\Interop\Hprimsante\Exceptions\CHPrimSanteExceptionWarning;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\CFileTraceability;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSenderFileSystem;
use Ox\Mediboard\System\CSourceFileSystem;

class HandleObservationResultFiles extends HandleObservationResult
{
    use TraceabilityTrait;

    /** @var DOMNode */
    private $OBR_node;
    /** @var DOMNode */
    private $OBX_node;
    /** @var DOMNode */
    private $P_node;

    /**
     * @param array $params
     *
     * @return void
     * @throws CHPrimSanteExceptionError
     * @throws CHPrimSanteExceptionWarning
     */
    public function handle(array $params): void
    {
        $this->params = $params;
        $OBR_node     = CMbArray::get($params, self::KEY_OBR_NODE);
        $OBX_node     = CMbArray::get($params, self::KEY_OBX_NODE);
        if (!$OBR_node || !$OBX_node) {
            $missing_segment = $OBR_node ? 'OBX' : 'OBR';
            throw  $this->makeImportantError($missing_segment, '18');
        }
        $this->P_node   = CMbArray::get($params, CHPrimSanteRecordORU::KEY_P_NODE);
        $this->OBR_node = $OBR_node->parentNode;
        $this->OBX_node = $OBX_node->parentNode;

        if ($this->message->_ref_sender->_configs['handle_oru_type'] === 'labo') {
            $this->laboratoryHandleFiles();
        } else {
            $this->defaultHandleFiles();
        }
    }

    /**
     * Return the object for attach the document
     *
     * @param String   $date         date
     * @param CPatient $patient      patient
     * @param String   $praticien_id praticien id
     * @param CSejour  $sejour       sejour
     *
     * @return CConsultation|COperation|CSejour
     * @throws Exception
     */
    private function getObjectWithDate(string $date, CPatient $patient, string $praticien_id, ?CSejour $sejour)
    {
        //Recherche de la consutlation dans le séjour
        $date         = CMbDT::date($date);
        $date_before  = CMbDT::date("- 2 DAY", $date);
        $consultation = new CConsultation();
        $where        = [
            "patient_id"           => "= '$patient->_id'",
            "annule"               => "= '0'",
            "plageconsult.date"    => "BETWEEN '$date_before' AND '$date'",
            "plageconsult.chir_id" => "= '$praticien_id'",
            "sejour_id"            => "= '$sejour->_id'",
        ];

        $leftjoin = ["plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id"];
        $consultation->loadObject($where, "plageconsult.date DESC", null, $leftjoin);

        //Recherche d'une consultation qui pourrait correspondre
        if (!$consultation->_id) {
            unset($where["sejour_id"]);
            $consultation->loadObject($where, "plageconsult.date DESC", null, $leftjoin);
        }

        //Consultation trouvé dans un des deux cas
        if ($consultation->_id) {
            return $consultation;
        }

        //Recherche d'une opération dans le séjour
        $where = [
            "sejour.patient_id" => "= '$patient->_id'",
            "plagesop.date"     => "BETWEEN '$date_before' AND '$date'",

            "operations.annulee" => "= '0'",
            "sejour.sejour_id"   => "= '$sejour->_id'",
        ];

        if ($praticien_id) {
            $where["operations.chir_id"] = "= '$praticien_id'";
        }

        $leftjoin  = [
            "plagesop" => "operations.plageop_id = plagesop.plageop_id",
            "sejour"   => "operations.sejour_id = sejour.sejour_id",
        ];
        $operation = new COperation();
        $operation->loadObject($where, "plagesop.date DESC", null, $leftjoin);

        if ($operation->_id) {
            return $operation;
        }

        return $sejour;
    }

    /**
     * @return void
     * @throws CHPrimSanteExceptionWarning
     */
    private function defaultHandleFiles(): void
    {
        $OBR_node = $this->OBR_node;
        $OBX_node = $this->OBX_node;
        if (!$this->message->sejour || !$this->message->sejour->_id) {
            throw $this->makeImportantError('P', '03', '8.5');
        }

        $result       = $this->message->getObservationResult($OBX_node);
        $result_parts = explode($this->component_separator, $result);
        $name_editor  = CMbArray::get($result_parts, 0);
        $file_name    = CMbArray::get($result_parts, 1);
        $file_type    = $this->message->getFileType(CMbArray::get($result_parts, 2));

        if (!$file_name) {
            throw $this->makeError('OBX', '04', '10.4');
        }

        $this->storeFile($name_editor, $file_name, $file_type);
    }

    /**
     * @return void
     * @throws CHPrimSanteExceptionError
     * @throws CHPrimSanteExceptionWarning
     */
    private function laboratoryHandleFiles(): void
    {
        $OBR_node = $this->OBR_node;
        $OBX_node = $this->OBX_node;

        $observation_result_set = $this->params[HandleObservation::KEY_OBSERVATION_RESULT_SET] ?? null;
        if (!$observation_result_set) {
            throw $this->makeImportantError('OBX', '19');
        }

        $result       = $this->message->getObservationResult($OBX_node);
        $result_parts = explode($this->component_separator, $result);
        $name_editor  = CMbArray::get($result_parts, 0);
        $file_name    = CMbArray::get($result_parts, 1);
        $file_type    = $this->message->getFileType(CMbArray::get($result_parts, 2));

        if (!$file_name) {
            throw $this->makeError('OBX', '04', '10.6');
        }

        $file = $this->storeFile($name_editor, $file_name, $file_type);

        // mark file like laboratory file
        if ($observation_result_set instanceof CObservationResultSet) {
            CObservationResultSet::markFileLabo($file, $observation_result_set->_id, $this->getSender());
        }
    }

    /**
     * @return CFileTraceability
     */
    private function generateTraceability(string $file_date): ?CFileTraceability
    {
        if (!$this->getSender()->_configs['mode_sas']) {
            return null;
        }

        $praticien = $this->message->getDoctor($this->P_node, true);
        $patient_identifiers = $this->message->identifier_patient;

        $patient = $this->message->mapPatientPrimary($this->P_node);
        $traceability = $this->generateTraceabilityHelper($this->getSender(), $patient);

        $traceability->IPP = $patient_identifiers['identifier'] ?? null;
        $traceability->NDA = $this->message->identifier_sejour["sejour_identifier"] ?? null;

        $traceability->received_datetime = $this->message->_ref_exchange_hpr->date_production;
        $traceability->datetime_object   = $file_date;
        $traceability->praticien_id      = $praticien && $praticien->_id ? $praticien->_id : null;

        return $traceability;
    }

    /**
     * @param CStoredObject|null $object
     * @param CFilesCategory     $files_category
     *
     * @return CFilesCategory|null
     * @throws Exception
     */
    protected function determineFileCategory(?CStoredObject $object, ?CFilesCategory $files_category): ?CFilesCategory
    {
        $sender = $this->getSender();

        $files_category_mb = null;
        if ($object instanceof CPatient && $sender->_configs["id_category_patient"] && ($sender->_configs["object_attach_OBX"] == "CPatient")) {
            $file_category_id = $sender->_configs["id_category_patient"];
            $category = new CFilesCategory();
            $category->load($file_category_id);
            if ($category->_id) {
                $files_category_mb = $category;
            }
        } elseif ($files_category && $files_category->_id && $sender->_configs["associate_category_to_a_file"]) {
            $files_category_mb = $files_category;
        }

        return $files_category_mb;
    }

    /**
     * Store the file
     *
     * @param String    $prefix    Prefix for the name of file
     * @param String    $file_name Name of file
     * @param String    $file_type Type file
     *
     * @return CFile
     * @throws CHPrimSanteExceptionError|CHPrimSanteExceptionWarning
     * @throws Exception
     */
    protected function storeFile($prefix, $file_name, $file_type): CFile
    {
        $sender = $this->message->_ref_sender;
        $target = $this->getTarget();

        if (!$target && !$this->isModeSAS()) {
            throw $this->makeImportantError('OBX', '03');
        }

        // On récupère toujours une seule catégorie, et une seule source associée à l'expéditeur
        /** @var CSenderFTP|CSenderSFTP|CSenderFileSystem $sender_link */
        $sender_link    = new CInteropSender();
        $files_category = new CFilesCategory();

        $object_links = $sender->loadRefsObjectLinks();

        foreach ($object_links as $_object_link) {
            if ($_object_link->_ref_object instanceof CFilesCategory) {
                $files_category = $_object_link->_ref_object;
            }

            if ($_object_link->_ref_object instanceof CInteropSender) {
                $sender_link = $_object_link->_ref_object;

                continue 1;
            }
        }

        // Aucun expéditeur permettant de récupérer les fichiers
        if (!$sender_link->_id) {
            throw $this->makeImportantError('OBX', '05', '10.4');
        }

        $sender_link->loadRefsExchangesSources();
        // Aucune source permettant de récupérer les fichiers
        if (!$sender_link->_id) {
            throw $this->makeImportantError('OBX', '06', '10.4');
        }

        /** @var CExchangeSource $_source */
        $source = $sender_link->getFirstExchangesSources();
        if ($source instanceof CSourceFileSystem) {
            /** @var CSourceFileSystem $_source */
            $path = $source->getFullPath($file_name);
        }
        if ($source instanceof CSourceFTP || $source instanceof CSourceSFTP) {
            /** @var CSourceFTP $_source */
            $path = $file_name;
        }

        // Exception déclenchée sur la lecture du fichier
        try {
            $content = $source->getClient()->getData("$path");
        } catch (Exception $e) {
            throw $this->makeError('OBX', '07', '10.4');
        }

        $files_category = $this->determineFileCategory($target, $files_category);

        // file_date
        if (CMbArray::get($this->getSender()->_configs, 'creation_date_file_like_treatment')) {
            $file_date = CMbDT::dateTime();
        } else {
            $file_date = $this->message->getOBXObservationDateTime($this->OBX_node);
            $file_date = CMbDT::dateTime($file_date);
        }

        // Gestion du CFile
        $file = new CFile();
        if ($target) {
            $file->setObject($target);
        }
        $file->file_name = "$prefix $file_name";
        $file->file_type = $file_type;
        $file->file_date = $file_date;
        $file->setContent($content);

        // store du file
        try {
            $file = (new FileManager($sender->_configs["define_name"]))
                ->enableTraceability($this->generateTraceability($file->file_date))
                ->setCategory($files_category)
                //->setTypeDmp($type_dmp) // todo dmp?
                ->setTag($sender->_self_tag)
                ->enableLoadMatching(true)
                ->store($file);
        } catch (FileManagerException $exception) {
            if ($exception->getId() === FileManagerException::INVALID_STORE_FILE) {
                throw $this->makeError('OBX', '08', '10.4', CMbString::removeAllHTMLEntities($exception->getMsg()));
            }

            throw $exception;
        }

        $this->message->addFileTreated($file);

        if ($sender_link->_delete_file !== false) {
            $source->getClient()->delFile($path);
        }

        return $file;
    }
}
