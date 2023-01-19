<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CAppUI;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Description
 */
class CCSVImportFilesCategory extends CMbCSVObjectImport {
  static $headers = array("nom", "nom_court", "class", "etablissement", "importance", "send_auto", "eligible_file_view", "medicale",
    "color");

  protected $line;

  function import() {
    $this->openFile();
    $this->setColumnNames();

    $this->current_line = 0;
    while ($this->line = $this->readAndSanitizeLine()) {
      $this->current_line++;

      if (!isset($this->line['nom'])) {
        CAppUI::setMsg('CFilesCategory-nom mandatory', UI_MSG_WARNING);
        continue;
      }

      $category = new CFilesCategory();
      $category->nom = trim($this->line['nom']);
      $category->loadMatchingObjectEsc();

      if ($category && $category->_id) {
        CAppUI::setMsg('CFilesCategory-msg-found', UI_MSG_OK);
        continue;
      }

      $category->bind($this->line);

      if (isset($this->line['etablissement'])) {
        $group_name = trim($this->line['etablissement']);

        $group = new CGroups();
        $group->_name = $group_name;

        $group->loadMatchingObjectEsc();


        if ($group && $group->_id) {
          $category->group_id = $group->_id;
        }
      }

      if ($msg = $category->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
        continue;
      }

      CAppUI::setMsg('CFilesCategory-msg-create', UI_MSG_OK);
    }
  }
}
