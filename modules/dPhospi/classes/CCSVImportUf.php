<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CApp;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Description
 */
class CCSVImportUf extends CMbCSVObjectImport {
  protected $count  = 0;
  protected $errors = 0;
  protected $found = 0;

  public function import() {
    $this->openFile();
    $this->setColumnNames();

    $group = CGroups::loadCurrent();

    while ($_line = $this->readAndSanitizeLine(false)) {
      CApp::log('Line', $_line);
      $_uf              = new CUniteFonctionnelle();
      $_uf->group_id    = $group->_id;
      $_uf->code        = $_line[0];
      CApp::log('UF-before', $_uf);
      $_uf->loadMatchingObjectEsc();

      if (!$_uf->_id) {
        $_uf->libelle     = $_line[1];
        $_uf->type        = $_line[2];
        $_uf->type_sejour = $_line[3];

        if ($msg = $_uf->store()) {
          $this->errors++;
        }
        else {
          $this->count++;
        }
      }
      else {
        CApp::log('UF', $_uf);
        $this->found++;
      }
    }
  }

  public function sanitizeLine($line) {
    $line = array_map("trim", $line);
    $line = array_map("utf8_decode", $line);

    return $line;
  }

  /**
   * @return int
   */
  public function getCount() {
    return $this->count;
  }

  /**
   * @return int
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * @return int
   */
  public function getFound() {
    return $this->found;
  }
}