<?php
/**
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Printing;

use Ox\Core\CMbObject;
use Ox\Mediboard\Files\CFile;

/**
 * Source Printer
 */
class CSourcePrinter extends CMbObject {
  // DB Fields
  public $name;
  public $host;
  public $port;
  public $printer_name;

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["name"]         = "str notNull";
    $props["host"]         = "text notNull";
    $props["port"]         = "num";
    $props["printer_name"] = "str notNull";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->name;
  }

  /**
   * Send a file to the printer
   *
   * @param CFile $file The file to print
   *
   * @return void
   */
  function sendDocument(CFile $file) {
  }
}
