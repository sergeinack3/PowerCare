<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Ox\Core\CAppUI;
use Ox\Core\FileUtil\CFileReader;
use Ox\Core\FileUtil\CFormattedFileReader;

/**
 * Description
 */
class CMedecinFileReader extends CFormattedFileReader {
  protected $separator = '|';
  protected $sanitize = ["parent::sanitizeLine", "utf8_decode"];

  /**
   * @param int $start
   *
   * @return void
   */
  protected function goToStart($start) {
    fseek($this->fp, $start);
  }
}
