<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Ox\Core\CMbObject;

/**
 * Description
 */
class CSearchThesaurusEntry extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $search_thesaurus_entry_id;

  // DB fields
  public $entry;
  public $types;
  public $titre;
  public $contextes;
  public $agregation;
  public $fuzzy;
  public $group_id;
  public $function_id;
  public $user_id;
  public $search_auto;

  public $_refs_targets;
  public $_cim_targets;
  public $_ccam_targets;
  public $_ngap_targets;
  public $_atc_targets;

  public static $contextes_types = ["generique", "pharmacie", "pmsi", "prescription"];

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "search_thesaurus_entry";
    $spec->key   = "search_thesaurus_entry_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props["entry"]       = "text seekable";
    $props["types"]       = "str maxLength|255";
    $props["titre"]       = "str maxLength|255 seekable";
    $props["contextes"]   = "enum list|" . implode("|", CSearchThesaurusEntry::$contextes_types) . " seekable";
    $props["group_id"]    = "ref class|CGroups back|search_thesaurus_entry";
    $props["function_id"] = "ref class|CFunctions back|search_thesaurus_entry";
    $props["user_id"]     = "ref class|CMediusers notNull back|search_thesaurus_entry";
    $props["agregation"]  = "enum list|0|1 default|0";
    $props["fuzzy"]       = "enum list|0|1 default|0";
    $props["search_auto"] = "enum list|0|1 default|0";

    return $props;
  }

  /**
   * Method to load targets
   *
   * @return array
   */
  function loadRefsTargets() {
    $this->_cim_targets  = $this->_ccam_targets = $this->_ngap_targets = $this->_atc_targets = array();
    $this->_refs_targets = $this->loadBackRefs("target_entry");
    if (is_null($this->_refs_targets)) {
      $this->_refs_targets = array();
    }

    /** @var CSearchTargetEntry $_target */
    foreach ($this->_refs_targets as $_target) {
      $_target->loadRefTarget();

      switch ($_target->object_class) {
        case "CCodeCIM10":
          $this->_cim_targets[] = $_target;
          break;
        case "CCodeCCAM":
          $this->_ccam_targets[] = $_target;
          break;
        case "CActeNGAP":
          $this->_ngap_targets[] = $_target;
          break;
        case "CMedicamentClasseATC":
          $this->_atc_targets[] = $_target;
          break;
        default:
          // nothing to do
          break;
      }
    }

    return $this->_refs_targets;
  }

}
