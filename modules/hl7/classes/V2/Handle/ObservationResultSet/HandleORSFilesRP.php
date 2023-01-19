<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle\ObservationResultSet;

use DOMNode;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Ftp\CSenderFTP;
use Ox\Interop\Ftp\CSenderSFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\Exceptions\V2\CHL7v2ExceptionWarning;
use Ox\Interop\Sas\CSAS;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Sante400\CHyperTextLink;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSenderFileSystem;
use Ox\Mediboard\System\CSourceFileSystem;
use Symfony\Component\HttpFoundation\ParameterBag;

class HandleORSFilesRP extends HandleORSFiles
{
    /**
     * @param ParameterBag $bag
     *
     * @throws CHL7v2Exception
     */
    public function handle(ParameterBag $bag): void
    {
        parent::handle($bag);

        $result_date  = $bag->get('result_date');

        // OBX.11 : Observation result status
        $status = $this->getObservationResultStatus($this->OBX);

        // Get name (-1, on prennait le key sur le foreach)
        $name = $this->determineName($this->OBR);

        // Reference Pointer File
        $this->getReferencePointerToExternalReport(
            $this->OBX,
            $this->target_object,
            $name,
            $result_date,
            $status
        );
    }

    /**
     * OBX Segment with reference pointer to external report
     *
     * @param DOMNode           $OBX               OBX node
     * @param CMbObject         $object            Object
     * @param String            $name              name
     * @param string            $dateTimeResult    Date
     * @param string            $status            Status
     *
     * @return void
     * @throws Exception
     */
    private function getReferencePointerToExternalReport(
        DOMNode $OBX,
        CMbObject $object,
        $name,
        $dateTimeResult,
        $status = null
    ): void {
        $sender = $this->sender;

        //Récupération de l'emplacement et du type du fichier (full path)
        $observation = $this->getObservationValue($OBX);

        $rp      = explode("^", $observation);
        $pointer = CMbArray::get($rp, 0);
        $type    = CMbArray::get($rp, 2);

        // Création d'un lien Hypertext sur l'objet
        if ($type == "HTML") {
            $hyperlink = new CHyperTextLink();
            $hyperlink->setObject($object);
            $hyperlink->name = $name;

            if ($sender->_configs["change_OBX_5"]) {
                $separators = explode("§§", $sender->_configs["change_OBX_5"]);
                $from       = CMbArray::get($separators, 0);
                $to         = CMbArray::get($separators, 1);

                $pointer = $from && $to ? preg_replace("#$from#", "$to", $pointer) : $pointer;
            }

            $hyperlink->link = $pointer;
            $hyperlink->loadMatchingObject();

            if ($msg = $hyperlink->store()) {
                throw (new CHL7v2ExceptionWarning('E343'))
                    ->setComments($msg)
                    ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
            }

            return;
        }

        // Où récupérer le nom du fichier ?
        if ($handle_file_name = CAppUI::gconf("hl7 ORU handle_file_name", $sender->group_id)) {
            $tab_senders = explode(",", $handle_file_name);
            foreach ($tab_senders as $_sender) {
                $temp = explode("|", $_sender);
                if (CMbArray::get($temp, 0) !== $sender->_guid) {
                    continue;
                }

                $OBX_path = CMbArray::get($temp, 1);
                // Search
                if ($search = $this->message->queryTextNode($OBX_path, $OBX)) {
                    if ($OBX_path == "OBX.5") {
                        $search_tab = explode("^", $search);
                        $search     = CMbArray::get($search_tab, 0);
                    }
                    $name = $search ?: (CMbArray::get(pathinfo($pointer), 'filename'));
                }
            }
        }

        // Est ce qu'on a déjà le GUID du fichier dans le message HL7
        $guid = explode('-', $pointer);
        if (CMbArray::get($guid, 0) && CMbArray::get($guid, 1)) {
            if (class_exists(CMbArray::get($guid, 0))) {
                $file = CMbObject::loadFromGuid($pointer);
                if ($file->_id) {
                    return;
                }
            }
        }

        // Chargement des objets associés à l'expéditeur
        /** @var CInteropSender $sender_link */
        $object_links = $sender->loadRefsObjectLinks();
        if (!$object_links) {
            throw (new CHL7v2ExceptionWarning('E340'))
                ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
        }

        $sender_link    = new CInteropSender();
        $files_category = new CFilesCategory();
        // On récupère toujours une seule catégorie, et une seule source associée à l'expéditeur
        foreach ($object_links as $_object_link) {
            if ($_object_link->_ref_object instanceof CFilesCategory) {
                $files_category = $_object_link->_ref_object;
            }

            if ($_object_link->_ref_object instanceof CInteropSender || $_object_link->_ref_object instanceof CExchangeSource) {
                $sender_link = $_object_link->_ref_object;

                continue 1;
            }
        }

        // Configuration d'une catégorie reçue
        if ($observation_sub_id = $this->getOBXObservationSubID($OBX)) {
            $idex = CIdSante400::getMatch(
                "CFilesCategory",
                CSAS::getFilesCategoryAssociationTag($sender->group_id),
                $observation_sub_id
            );
            if ($idex->_id) {
                $files_category->load($idex->object_id);
            }
        }

        // Aucun expéditeur permettant de récupérer les fichiers
        if (!$sender_link->_id) {
            throw (new CHL7v2ExceptionWarning('E340'))
                ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
        }

        $authorized_sources = [
            "CSenderFileSystem",
            "CSenderFTP",
            "CSenderSFTP",
            "CSourceFileSystem",
            "CSourceFTP",
            "CSourceSFTP",
        ];

        // L'expéditeur n'est pas prise en charge pour la réception de fichiers
        if (!CMbArray::in($sender_link->_class, $authorized_sources)) {
            throw (new CHL7v2ExceptionWarning('E341'))
                ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
        }

        /** @var CSenderFileSystem|CSenderFTP|CSenderSFTP $sender_link */
        if ($sender_link instanceof CInteropSender) {
            $sender_link->loadRefsExchangesSources();
            // Aucune source permettant de récupérer les fichiers
            if (!$sender_link->_id) {
                throw (new CHL7v2ExceptionWarning('E342'))
                    ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
            }

            $source = $sender_link->getFirstExchangesSources();
        } elseif ($sender_link instanceof CExchangeSource) {
            $source = $sender_link;
        }

        $path  = str_replace("\\", "/", $pointer);
        $path  = basename($path);
        $infos = pathinfo($path);

        if ($source instanceof CSourceFileSystem) {
            $path = $source->getFullPath() . "/$path";
        }

        // Exception déclenchée sur la lecture du fichier
        try {
            if ($source instanceof CSourceSFTP) {
                $content = $source->getClient()->getData("$path", true);
            } else {
                $content = $source->getClient()->getData("$path");
            }
        } catch (Exception $e) {
            throw (new CHL7v2ExceptionWarning('E345'))
                ->setPosition("OBSERVATION[$this->observation_index]/OBX[$this->OBX_index]");
        }

        if (!$type) {
            $type = CMbPath::getExtension($path);
        }

        $ext       = CMbArray::get($infos, "extension");
        $file_type = $ext ? CMbPath::guessMimeType($pointer, strtolower($ext)) : $this->message->getFileType($type);

        $this->storeFile(
            $object,
            $files_category,
            $dateTimeResult,
            $name,
            $ext,
            $file_type,
            $content,
            $status
        );

        /** @var CSenderFileSystem|CSenderFTP|CSenderSFTP $sender */
        $sender = $this->sender;
        if (($sender instanceof CInteropSender && $sender->after_processing_action === "delete")) {
            if ($source instanceof CSourceSFTP) {
                $source->getClient()->delFile($path);
            } else {
                $source->getClient()->delFile($path);
            }
        }

        $this->addCode("I340");
    }
}
