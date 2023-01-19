<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;

/**
 * Description
 */
class CXDSSubmissionLot extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $cxds_submissionlot_id;
  public $title;
  public $comments;
  public $date;
  public $type;

  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "cxds_submissionlot";
    $spec->key    = "cxds_submissionlot_id";
    return $spec;  
  }
  
  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["title"]    = "str";
    $props["comments"] = "str";
    $props["date"]     = "dateTime";
    $props["type"]     = "str notNull";

    return $props;
  }
}