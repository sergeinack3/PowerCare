<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Data;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * The Dicom presentation context
 */
class CDicomPresentationContext implements IShortNameAutoloadable {
  
  /**
   * The id of the presentation context
   * 
   * @var integer
   */
  public $id;
  
  /**
   * The abstract syntax
   * 
   * @var string
   */
  public $abstract_syntax;
  
  /**
   * The transfer syntax
   * 
   * @var string
   */
  public $transfer_syntax;

  /**
   * The constructor
   *
   * @param integer $id              The id
   *
   * @param string  $abstract_syntax The abstract syntax
   *
   * @param string  $transfer_syntax The transfer syntax
   *
   * @return void
   */
  function __construct($id, $abstract_syntax, $transfer_syntax = null) {
    $this->id = $id;
    $this->abstract_syntax = $abstract_syntax;
    $this->transfer_syntax = $transfer_syntax;
  }
}