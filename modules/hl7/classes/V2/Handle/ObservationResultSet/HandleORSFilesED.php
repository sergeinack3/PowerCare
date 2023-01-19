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
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Sas\CSAS;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\CFileTraceability;
use Ox\Mediboard\Sante400\CIdSante400;
use Symfony\Component\HttpFoundation\ParameterBag;

class HandleORSFilesED extends HandleORSFiles
{

    /**
     * @param ParameterBag $bag
     *
     * @throws CHL7v2Exception
     * @throws Exception
     */
    public function handle(ParameterBag $bag): void
    {
        parent::handle($bag);

        $result_date  = $bag->get('result_date');

        // OBX.11 : Observation result status
        $status = $this->getObservationResultStatus($this->OBX);

        // Get name (-1, on prennait le key sur le foreach)
        $name = $this->determineName($this->OBR);

        // Encapsulated Data
        $this->getEncapsulatedData(
            $this->OBX,
            $this->target_object,
            $name,
            $result_date,
            $status
        );
    }

    /**
     * OBX Segment with encapsulated Data
     *
     * @param DOMNode           $OBX               OBX node
     * @param CMbObject         $object            Object
     * @param String            $name              name
     * @param string            $dateTimeResult    Date
     * @param CFileTraceability $file_traceability Traçabilité du fichier
     * @param string            $status            Status
     *
     * @return void
     * @throws Exception
     */
    private function getEncapsulatedData(
        $OBX,
        $object,
        $name,
        $dateTimeResult,
        $status = null
    ): void {
        $sender = $this->sender;

        //Récupération de le fichier et du type du fichier (basename)
        $observation = $this->getObservationValue($OBX);

        $ed      = explode("^", $observation);
        $pointer = CMbArray::get($ed, 0);
        $type    = CMbArray::get($ed, 2);

        $content = base64_decode(CMbArray::get($ed, 4));

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
        $file_guid = CMbArray::get($ed, 4);
        $guid      = explode('-', $file_guid);
        if (CMbArray::get($guid, 0) && CMbArray::get($guid, 1)) {
            if (class_exists(CMbArray::get($guid, 0))) {
                $file = CMbObject::loadFromGuid($file_guid);
                if ($file && $file->_id) {
                    return;
                }
            }
        }

        $files_category = new CFilesCategory();
        // Chargement des objets associés à l'expéditeur
        // On récupère toujours une seule catégorie, et une seule source associée à l'expéditeur
        foreach ($sender->loadRefsObjectLinks() as $_object_link) {
            if ($_object_link->_ref_object instanceof CFilesCategory) {
                $files_category = $_object_link->_ref_object;
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

        $ext       = strtolower($type);
        $file_type = $this->message->getFileType($ext);

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
    }
}
