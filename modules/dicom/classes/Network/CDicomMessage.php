<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Network;

use Ox\Interop\Eai\CInteropNorm;

/**
 * DICOM message family
 */
class CDicomMessage extends CInteropNorm {
  /**
   * The constructor
   *
   * @return void
   */
  function __construct() {
    $this->name = "CDicomMessage";

    parent::__construct();
  }
}
