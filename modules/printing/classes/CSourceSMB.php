<?php
/**
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Printing;

use Ox\Core\CSMB;
use Ox\Mediboard\Files\CFile;

/**
 * Source SMB
 */
class CSourceSMB extends CSourcePrinter {
  // DB Table key
  public $source_smb_id;

  // DB Fields
  public $user;
  public $password;
  public $workgroup;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'source_smb';
    $spec->key   = 'source_smb_id';

    return $spec;
  }

  /**
   * @sinheritdoc
   */
  function getProps() {
    $props              = parent::getProps();
    $props["user"]      = "str";
    $props["password"]  = "password revealable";
    $props["workgroup"] = "str";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function sendDocument(CFile $file) {
    $smb = new CSMB();
    $smb->init($this);
    $smb->printFile($file);
  }
}
