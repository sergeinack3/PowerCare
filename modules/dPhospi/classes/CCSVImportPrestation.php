<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Description
 */
class CCSVImportPrestation extends CMbCSVObjectImport {
  protected $dryrun;
  protected $update;
  protected $results = array();
  protected $unfound = array();
  protected $line;
  protected $presta_updates = array();

  /**
   * @inheritdoc
   */
  function __construct($file_path, $dryrun, $update, $start = 0, $step = 100, $profile = CCSVFile::PROFILE_EXCEL) {
    parent::__construct($file_path, $start, $step, $profile);
    $this->dryrun = $dryrun;
    $this->update = $update;
  }

  /**
   * @inheritdoc
   */
  function import() {
    $this->openFile();
    $this->setColumnNames();
    $this->current_line = 0;

    $group = CGroups::loadCurrent();

    while ($this->line = $this->readAndSanitizeLine()) {
      $this->current_line++;

      if (!isset($this->line['prestation']) || !$this->line['prestation']) {
        CAppUI::stepAjax('mod-dPhospi-import-presta-mandatory-line-%d', UI_MSG_ERROR, $this->current_line);
      }

      if (!isset($this->line['type']) || ($this->line['type'] != 'journaliere' && $this->line['type'] != 'ponctuelle')) {
        CAppUI::stepAjax('mod-dPhospi-import-type-presta-line-%d', UI_MSG_ERROR, $this->current_line);
      }

      $this->results[$this->current_line] = $this->line;

      $type_presta = ($this->line['type'] == 'journaliere') ? new CPrestationJournaliere() : new CPrestationPonctuelle();

      /** @var CPrestationExpert $prestation */
      $prestation           = new $type_presta();
      $prestation->nom      = $this->line['prestation'];
      $prestation->group_id = $group->_id;
      $prestation->repair();

      $prestation->loadMatchingObjectEsc();

      $prestation->type_hospi = ($this->line['type_admission']) ? $this->line['type_admission'] : '';
      $prestation->M          = $this->line['M'];
      $prestation->C          = $this->line['C'];
      $prestation->O          = $this->line['O'];
      $prestation->SSR        = $this->line['SSR'];

      if ($this->dryrun) {
        continue;
      }

      $new = $prestation->_id ? 'modify' : 'create';
      if ((!$prestation->_id || ($prestation->_id && $this->update)) && !array_key_exists($prestation->_id, $this->presta_updates)) {
        if ($msg = $prestation->store()) {
          CAppUI::stepAjax($msg, UI_MSG_WARNING);
          $this->results[$this->current_line]['error'] = $msg;
          continue;
        }

        $this->presta_updates[$prestation->_id] = true;
        CAppUI::stepAjax("$prestation->_class-msg-$new", UI_MSG_OK);
      }

      $item      = new CItemPrestation();
      $item->nom = $this->line['item'];
      $item->setObject($prestation);
      $item->rank = $this->line['rang'];

      $item->repair();
      $item->loadMatchingObjectEsc();

      if ($price = CMbArray::get($this->line, "price")) {
        $item->price = $price;
      }

      $new = ($item->_id) ? 'modify' : 'new';
      if (!$item->_id || ($item->_id && $this->update)) {
        if ($msg = $item->store()) {
          CAppUI::stepAjax($msg, UI_MSG_WARNING);
          $this->results[$this->current_line]['error'] = $msg;
          continue;
        }

        CAppUI::stepAjax("CItemPrestation-msg-$new", UI_MSG_OK);
      }

      if (CMbArray::get($this->line, "identifiant_externe")) {
        $idexs = explode('||', $this->line['identifiant_externe']);
        foreach ($idexs as $_idx) {
          $id          = explode('|', $_idx);
          $idex        = new CIdSante400();
          $idex->id400 = $id[0];
          $idex->tag   = $id[1];
          $idex->setObject($item);

          $idex->loadMatchingObjectEsc();

          if (!$idex->_id) {
            if ($msg = $idex->store()) {
              CAppUI::stepAjax($msg, UI_MSG_WARNING);
              $this->results[$this->current_line]['error'] = $msg;
              continue;
            }

            CAppUI::stepAjax("CIdSante400-msg-create", UI_MSG_OK);
          }
        }
      }
    }

    $this->csv->close();
  }

  /**
   * @return array
   */
  function getResults() {
    return $this->results;
  }
}
