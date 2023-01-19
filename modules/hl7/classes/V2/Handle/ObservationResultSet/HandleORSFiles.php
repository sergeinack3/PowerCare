<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Eai\Manager\Exceptions\FileManagerException;
use Ox\Interop\Eai\Manager\FileManager;
use Ox\Interop\Eai\Tools\TraceabilityTrait;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionWarning;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\CFileTraceability;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CHyperTextLink;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class HandleORSRFilesSAS
 *
 * @package Ox\Interop\Hl7\V2\Handle\ObservationResultSet
 */
abstract class HandleORSFiles extends HandleORSOBX
{
    use TraceabilityTrait;

    /** @var string */
    private $praticien_id;

    /**
     * @param ParameterBag $bag
     *
     * @throws Exception
     */
    public function handle(ParameterBag $bag): void
    {
        parent::handle($bag);

        // OBX.16 : Identifiant du praticien
        if ($praticien_id = $this->getObservationAuthor($this->OBX)) {
            $this->praticien_id = $praticien_id;
            $bag->set('praticien_id', $praticien_id);
        }
    }

    /**
     * @param string|null $OBR_name
     *
     * @return string
     * @throws Exception
     */
    protected function determineName(ParameterBag $OBR): string
    {
        // OBR name pour Tamm SIH
        if (CMbArray::get($this->sender->_configs, "handle_tamm_sih")) {
            return $this->getOBRServiceIdentifier($OBR, 'CE.2');
        }

        // On prend l'observation file name si elle existe
        $OBX_3 = $this->message->getObservationFilename($this->OBX);

        // sinon on prend le code fournit dans le OBR.4
        $name  = $OBX_3 ?: $this->getOBRServiceIdentifier($OBR);

        // Si y a plusieurs OBX, index le nom de l'observation
        if (count($this->observation->get('OBX')) > 1) {
            $name = $name . $this->OBX_index;
        }

        return $name;
    }

    /**
     * @return CFileTraceability
     * @throws Exception
     */
    protected function generateTraceability(string $file_date): ?CFileTraceability
    {
        if (!$this->isModeSAS()) {
            return null;
        }

        // Récupération des informations du patient du message
        $patient = new CPatient();
        $this->message->getPID($this->data["PID"], $patient);

        $traceability = $this->generateTraceabilityHelper($this->sender, $patient);
        $traceability->received_datetime = $this->exchange_hl7v2->date_production;
        $traceability->IPP               = $this->message->getPatientPI();
        $traceability->NDA = $this->message->venueAN;
        $traceability->datetime_object = $file_date;
        $traceability->praticien_id = $this->praticien_id;

        return $traceability;
    }

    /**
     * Determine the category for associate it to the file
     *
     * @param CInteropSender     $sender
     * @param CStoredObject|null $object
     * @param CFilesCategory     $files_category
     *
     * @return CFilesCategory|null
     * @throws Exception
     */
    protected function determineFileCategory(CInteropSender $sender, ?CStoredObject $object, CFilesCategory $files_category): ?CFilesCategory
    {
        $files_category_mb = null;
        if ($object instanceof CPatient && $sender->_configs["id_category_patient"] && ($sender->_configs["object_attach_OBX"] == "CPatient")) {
            $file_category_id = $sender->_configs["id_category_patient"];
            $category = new CFilesCategory();
            $category->load($file_category_id);
            if ($category->_id) {
                $files_category_mb = $category;
            }
        } elseif ($files_category->_id && $sender->_configs["associate_category_to_a_file"]) {
            $files_category_mb = $files_category;
        }

        return $files_category_mb;
    }

    /**
     * Store external file
     *
     * @param CMbObject         $object            Object
     * @param CFilesCategory    $files_category    Files category
     * @param int               $set_id            Set id
     * @param string            $dateTimeResult    DateTime result
     * @param string            $name              File name
     * @param string            $ext               File extension
     * @param string            $file_type         File type
     * @param string            $content           Content
     * @param string            $status            Status
     *
     * @return bool
     * @throws CHL7v2Exception|Exception
     */
    protected function storeFile(
        $object,
        CFilesCategory $files_category,
        $dateTimeResult,
        $name,
        $ext,
        $file_type,
        $content,
        $status = null
    ) {
        $exchange_hl7v2 = $this->exchange_hl7v2;
        $sender         = $this->sender;
        $id_partner     = $this->OBR->get(self::OBR_ID_PARTNER);

        // Si on rattache le document sur un patient et qu'on a les configs
        $files_category_mb = $this->determineFileCategory($sender, $object, $files_category);

        // gestion de l'extension
        if (strpos($name, "." . strtolower($ext)) === false) {
            $name .= "." . strtolower($ext);
        }

        $file = new CFile();
        // set meta
        if ($object) {
            $file->setObject($object);
        }
        $file->file_name = $name;
        $file->file_type = $file_type;
        $file->file_date = $dateTimeResult;
        $file->annule    = $status == 'X' ? 1 : 0;
        $file->setContent($content);

        try {
            // store file and trace if enabled
            $file = (new FileManager($sender->_configs["define_name"]))
                ->setTypeDmp($this->getOBXObservationIdentifier($this->OBX))
                ->setCategory($files_category_mb)
                ->setTag($sender->_tag_hl7)
                ->enableLoadMatching($id_partner ?: true)
                ->enableTraceability($this->generateTraceability($dateTimeResult))
                ->store($file);
        } catch (FileManagerException $exception) {
            $mapping_exception = [
              FileManagerException::FILE_CONTEXT_DIVERGENCE => 'E349',
              FileManagerException::CONTENT_EMPTY           => 'E346',
              FileManagerException::NO_TARGET_OBJECT        => 'E347',
              FileManagerException::INVALID_STORE_FILE      => 'E343',
              FileManagerException::INVALID_STORE_IDEX      => 'E348',
            ];

            $msg = $exception->getMsg();
            if ($code = ($mapping_exception[$exception->getId()] ?? null)) {
                $exception = (new CHL7v2ExceptionWarning($code))
                    ->setComments($msg)
                    ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
            }

            throw $exception;
        }

        // handle Laboratory
        if ($this->observation_result_set) {
            // mark file with an idex for Labo sync files
            CObservationResultSet::markFileLabo($file, $this->observation_result_set->_id);

            // here send doc ox labo to mb
            if (!CModule::getActive('oxLaboClient') && CModule::getActive('oxLaboServer')) {

            }
        }

        // Suppression du lien dans le message et remplacement par le GUID du CFile
        $hl7_message = new CHL7v2Message;
        $hl7_message->parse($exchange_hl7v2->_message);

        /** @var CHL7v2MessageXML $xml */
        $xml = $hl7_message->toXML(null, true);
        $set_id = ($this->OBX_index + 1);
        $xml = CHL7v2Message::setIdentifier(
            $xml,
            "//ORU_R01.OBSERVATION[" . $set_id . "]/OBX[1]",
            $file->_guid,
            "OBX.5",
            null,
            null,
            $this instanceof HandleORSFilesRP  ? 1 : 5,
            "^"
        );

        $exchange_hl7v2->_message = $xml->toER7($hl7_message);
        $exchange_hl7v2->store();

        if ($pointer = $sender->_configs["handle_context_url"]) {
            $hyperlink = new CHyperTextLink();
            $hyperlink->setObject($object);
            $hyperlink->name = $name;

            /** @var CPatient $patient */
            if ($object instanceof CPatient) {
                $patient = $object;
            } else {
                $patient = $object->loadRefPatient();
            }
            $patient->loadIPP($sender->group_id);

            $searches = [
                "[IPP]",
            ];

            $replaces = [
                $patient->_IPP,
            ];

            $pointer         = str_replace($searches, $replaces, $pointer);
            $hyperlink->link = $pointer;
            $hyperlink->loadMatchingObject();

            if ($msg = $hyperlink->store()) {
                throw (new CHL7v2ExceptionWarning('E343'))
                    ->setComments($msg)
                    ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
            }
        }

        return true;
    }
}
