<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Description
 */
class CCSVImportInfrastructure extends CMbCSVObjectImport {
  protected $results = array();
  protected $group_id;
  protected $line;

  /**
   * @inheritdoc
   */
  function import() {
    $this->openFile();
    $this->setColumnNames();

    $this->group_id = CGroups::loadCurrent()->_id;

    while ($this->line = $this->readAndSanitizeLine()) {
      $this->current_line++;

      $this->results[$this->current_line] = $this->line;

      $service = $this->importService();
      if (!$service) {
        continue;
      }

      $chambre = $this->importChambre($service);
      if (!$chambre) {
        continue;
      }

      $lit = $this->importLit($chambre);
      if (!$lit) {
        continue;
      }

      $this->importPrestaLink($lit);
    }

    $this->csv->close();
  }

  /**
   * Import a CService
   *
   * @return CService
   */
  function importService() {
    $service           = new CService();
    $service->nom      = $this->line['service'];
    $service->group_id = $this->group_id;
    $service->loadMatchingObjectEsc();

    if (!$service->_id) {
      if ($msg = $service->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
        $this->results[$this->current_line]["error"] = $msg;

        return null;
      }

      CAppUI::setMsg("CService-msg-create", UI_MSG_OK);
    }

    return $service;
  }

  /**
   * Import a CChambre object
   *
   * @param CService $service Service for the CChambre
   *
   * @return CChambre
   */
  function importChambre($service) {
    $chambre             = new CChambre();
    $chambre->nom        = $this->line['chambre'];
    $chambre->service_id = $service->_id;
    $chambre->loadMatchingObjectEsc();

    if (!$chambre->_id) {
      $chambre->annule = 0;

      if ($msg = $chambre->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
        $this->results[$this->current_line]["error"] = $msg;

        return null;
      }

      CAppUI::setMsg("CChambre-msg-create", UI_MSG_OK);
    }

    return $chambre;
  }

  /**
   * Import a CLit object
   *
   * @param CChambre $chambre CChambre object for the CLit
   *
   * @return CLit
   */
  function importLit($chambre) {
    $lit             = new CLit();
    $lit->nom        = $this->line['lit'];
    $lit->chambre_id = $chambre->_id;
    $lit->loadMatchingObjectEsc();

    if (!$lit->_id) {
      $lit->nom_complet = $this->line['lit_complet'];
      if ($msg = $lit->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
        $this->results[$this->current_line]["error"] = $msg;

        return null;
      }

      CAppUI::setMsg("CLit-msg-create", UI_MSG_OK);
    }

    return $lit;
  }

  /**
   * Import CItemPrestation link to CLit
   *
   * @param CLit $lit Clit object
   *
   * @return void
   */
  function importPrestaLink($lit) {
    if (!isset($this->line['prestas']) || !$this->line['prestas']) {
      return;
    }

    $prestas = explode('|', $this->line['prestas']);
    foreach ($prestas as $_presta) {
      $item               = new CItemPrestation();
      $item->nom          = $_presta;
      $item->object_class = 'CPrestationJournaliere';
      $items              = $item->loadMatchingListEsc();

      $presta_item = null;
      /** @var CItemPrestation $_item */
      foreach ($items as $_item) {
        $presta_journaliere = $_item->loadRefObject();
        if (!$presta_journaliere || !$presta_journaliere->_id || !$presta_journaliere->group_id == $this->group_id) {
          continue;
        }
        else {
          $presta_item = $_item;
          break;
        }
      }

      if (!$presta_item || !$presta_item->_id) {
        CAppUI::setMsg('dPhospi-import-CItemPrestation not found %s', UI_MSG_WARNING, $_presta);
        if (!array_key_exists('error', $this->results[$this->current_line])) {
          $this->results[$this->current_line]['error'] = '';
        }

        $this->results[$this->current_line]['error'] .= CAppUI::tr('dPhospi-import-CItemPrestation not found %s', $_presta) . "\n";
        continue;
      }

      $link                     = new CLitLiaisonItem();
      $link->lit_id             = $lit->_id;
      $link->item_prestation_id = $presta_item->_id;
      $link->loadMatchingObjectEsc();

      if (!$link->_id) {
        if ($msg = $link->store()) {
          CAppUI::setMsg($msg, UI_MSG_WARNING);
          $this->results[$this->current_line]['error'] = $msg;
          continue;
        }

        CAppUI::setMsg('CLitLiaisonItem-msg-create', UI_MSG_OK);
      }
    }
  }

  /**
   * @inheritdoc
   */
  function readAndSanitizeLine($assoc = true, $nullify_enpty_values = true) {
    $line = parent::readAndSanitizeLine($assoc, $nullify_enpty_values);
    if ($line) {
      array_walk($line, 'trim');
    }

    return $line;
  }

  /**
   * @return array
   */
  function getResults() {
    return $this->results;
  }
}
