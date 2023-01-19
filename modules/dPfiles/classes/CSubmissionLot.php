<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;

/**
 * Description
 */
class CSubmissionLot extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $submissionlot_id;
  public $title;
  public $comments;
  public $date;
  public $type;

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "submissionlot";
    $spec->key    = "submissionlot_id";
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
    $props["type"]     = "enum list|XDS|DMP|FHIR|SISRA|ANS default|XDS notNull";

    return $props;
  }
}
