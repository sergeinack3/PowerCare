<?php
/**
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Printing;

use Ox\Core\CLPR;
use Ox\Mediboard\Files\CFile;

/**
 * Source LPR
 */
class CSourceLPR extends CSourcePrinter {
  // DB Table key
  public $source_lpr_id;

  // DB Fields
  public $user;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'source_lpr';
    $spec->key   = 'source_lpr_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["user"]         = "str";
    $props["printer_name"] = "str";

    return $props;
  }

  /**
   * @see parent::sendDocument()
   */
  function sendDocument(CFile $file) {
    $lpr = new CLPR();
    $lpr->init($this);
    $lpr->printFile($file);
  }
}
