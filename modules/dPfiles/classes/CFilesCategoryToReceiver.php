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
use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Erp\CabinetSIH\CCabinetSIH;
use Ox\Erp\CabinetSIH\CCabinetSIHSas;
use Ox\Interop\Dmp\CDMPRequest;
use Ox\Interop\Dmp\CDMPSas;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\SIHCabinet\CSIHCabinet;
use Ox\Interop\SIHCabinet\CSIHCabinetSas;
use Ox\Interop\Sisra\CSisraRequest;
use Ox\Interop\Sisra\CZepraSas;
use Ox\Interop\Xds\CXDSRequest;

/**
 * Description
 */
class CFilesCategoryToReceiver extends CStoredObject {
  /**
   * @var integer Primary key
   */
  public $files_category_to_receiver_id;

  public $receiver_class;
  public $receiver_id;
  public $files_category_id;
  public $active;
  public $description;

  /** @var CFilesCategory */
  public $_ref_files_category;
  /** @var CInteropReceiver */
  public $_ref_receiver;

  /** @var string */
  public $type;

  /**
   * @inheritDoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "files_category_to_receiver";
    $spec->key    = "files_category_to_receiver_id";
    $spec->uniques["receiver_link"] = array("receiver_id", "receiver_class", "files_category_id", "type");
    $spec->xor["context"] = ["receiver_id", "type"];
    return $spec;
  }
  
  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["receiver_id"]       = "ref class|CInteropReceiver meta|receiver_class back|files_categories";
    $props["receiver_class"]    = "str maxLength|80";
    $props["files_category_id"] = "ref notNull class|CFilesCategory back|related_receivers";
    $props["active"]            = "bool notNull default|1";
    $props["description"]       = "text";
    $props["type"]              = "str";

    return $props;
  }

  /**
   * Load interop receiver
   *
   * @return CInteropReceiver|CStoredObject
   * @throws Exception
   */
  function loadRefReceiver() {
    if ($receiver = $this->loadFwdRef("receiver_id", true)) {
      return $this->_ref_receiver = $receiver;
    }

    return $this->_ref_receiver = new CInteropReceiver();
  }

  /**
   * Load files category
   *
   * @return CFilesCategory|CStoredObject
   * @throws Exception
   */
  function loadRefFilesCategory() {
    return $this->_ref_files_category = $this->loadFwdRef("files_category_id", true);
  }

  /**
   * Get available receivers
   *
   * @param CDocumentItem $docItem      Document item
   * @param int           $group_id     group id
   * @param bool          $with_mssante Add mssante
   *
   * @throws Exception
   * @return array Receivers
   */
  static function getAvailableReceivers(CDocumentItem $docItem = null, $group_id = null, bool $with_mssante = false) {
    $receivers = array();

    // Partage vers le SISRA
    if (CModule::getActive("sisra")) {
      $receivers["sisra"] = CSisraRequest::getDocumentRepository($group_id);

      foreach ($receivers["sisra"] as $_receiver) {
        if ($docItem && !CZepraSas::doshareSisra($_receiver, $docItem)) {
          unset($receivers["sisra"][$_receiver->_guid]);
        }
      }
    }

    // Partage vers AppFine
    if (CModule::getActive("appFineClient")) {
      $receivers["appFineClient"] = CAppFineClient::getReceiversHL7v2Server(array("CHL7EventORUR01"), $group_id);

      foreach ($receivers["appFineClient"] as $_receiver) {
        if ($docItem && !CAppFineClientSas::doShareAppFine($_receiver, $docItem)) {
          unset($receivers["appFineClient"][$_receiver->_guid]);
        }
      }
    }

    // Partage vers TAMM-SIH
    if (CModule::getActive("oxSIHCabinet")) {
      self::getAvailableReceiversSIHCabinet($receivers, $docItem, $group_id);
    }

      // Partage vers SIH-TAMM
      if (CModule::getActive("oxCabinetSIH")) {
          self::getAvailableReceiversCabinetSIH($receivers, $docItem, $group_id);
      }

    // Partage vers le DMP
    if (CModule::getActive("dmp")) {
      $receiver_dmp = CDMPRequest::getDocumentRepository($group_id);
      if (($docItem && $receiver_dmp && $receiver_dmp->_id && CDMPSas::doShareDMP($receiver_dmp, $docItem, true))
          || (!$docItem && $receiver_dmp && $receiver_dmp->_id)
      ) {
        $receivers["dmp"][] = $receiver_dmp;
      }
    }

    if (CModule::getActive("medimail") && $with_mssante) {
        $receivers["medimail"] = ["MSSantePro", "MSSantePatient"];
    }

    // Partage vers un acteur XDS
    if (CModule::getActive("xds")) {
      // todo ajouter les destinataires XDS avec les détails associés
      $receivers["xds"] = CXDSRequest::getDocumentRepository($group_id);
    }

    return $receivers;
  }

    /**
     * @param                    $receivers
     * @param CDocumentItem|null $docItem
     * @param null               $group_id
     *
     * @return array
     * @throws Exception
     */
    public static function getAvailableReceiversCabinetSIH(
        &$receivers,
        CDocumentItem $docItem = null,
        $group_id = null
    ): ?array {
        // Dans le cas ou on a pas de docItem (paramétrage du destinataire sur la catégorie)
        //, on retourne tous les receivers Cabinet-SIH sans filtrer par SIH
        // Le filtrage par SIH se fera au moment de créer la trace
        if (!$docItem) {
            return $receivers["oxCabinetSIH"][] = CCabinetSIH::getReceivers('CHL7EventORUR01', $group_id);
        }

        // Envoi pas les événements patients qui n'ont pas un idex Cabinet-SIH
        $idex = CCabinetSIH::loadIdex($docItem->loadTargetObject());
        if (!$idex->_id) {
            return $receivers;
        }

        $sih_id = $idex->id400;

        $receiver_sih_cabinet = CCabinetSIH::getReceiversHL7v2('CHL7EventORUR01', $group_id, $sih_id);
        if (
            ($receiver_sih_cabinet && $receiver_sih_cabinet->_id
                && CCabinetSIHSas::doShareCabinetSIH($receiver_sih_cabinet, $docItem))
            || (!$docItem && $receiver_sih_cabinet && $receiver_sih_cabinet->_id)
        ) {
            $receivers["oxCabinetSIH"][] = $receiver_sih_cabinet;
        }

        return $receivers;
    }

  /**
   * Get available receivers SIH-Cabinet
   *
   * @param array         $receivers Receivers
   * @param CDocumentItem $docItem   Document item
   * @param int           $group_id  Group ID
   *
   * @throws Exception
   * @return array Receivers
   */
  static function getAvailableReceiversSIHCabinet(&$receivers, CDocumentItem $docItem = null, $group_id = null) {
    // Dans le cas ou on a pas de docItem (paramétrage du destinataire sur la catégorie), on retourne tous les receivers SIH-Cabinet sans filtrer par cabinet
    // Le filtrage par cabinet se fera au moment de créer la trace
    if (!$docItem) {
      return $receivers["oxSIHCabinet"][] = CSIHCabinet::getReceivers('CHL7EventORUR01', $group_id);
    }

    // Envoi pas les consultations qui n'ont pas un idex SIH-Cabinet
    $idex = CSIHCabinet::loadIdex($docItem->loadTargetObject());
    if (!$idex->_id) {
      return $receivers;
    }

    $cabinet_id = $idex->id400;

    $receiver_sih_cabinet = CSIHCabinet::getReceiversHL7v2('CHL7EventORUR01', $group_id, $cabinet_id);
    if (($receiver_sih_cabinet && $receiver_sih_cabinet->_id
        && CSIHCabinetSas::doShareSIHCabinet($receiver_sih_cabinet, $docItem))
        || (!$docItem && $receiver_sih_cabinet && $receiver_sih_cabinet->_id)
    ) {
      $receivers["oxSIHCabinet"][] = $receiver_sih_cabinet;
    }
      return $receivers;
  }
}
