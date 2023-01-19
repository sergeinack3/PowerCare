<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
/**
 * Class CHL7v2TableDescription
 * HL7 Table Description
 */
class CHL7v2TableDescription extends CHL7v2TableObject {
  // DB Table key
  public $table_description_id;

  // DB Fields
  public $number;
  public $description;
  public $user;
  public $valueset_id;

  // Form fields
  /** @var CHL7v2TableEntry[] $_entries */
  public $_entries;
  public $_count_entries;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table       = 'table_description';
    $spec->key         = 'table_description_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    // DB Fields
    $props["number"]      = "num notNull maxLength|5 seekable";
    $props["description"] = "str maxLength|80 seekable";
    $props["user"]        = "bool notNull default|0";
    $props["valueset_id"] = "str maxLength|80";

    // Form fields
    $props["_count_entries"] = "num";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view      = $this->description;
    $this->_shortview = $this->number;
  }

  /**
   * @return CHL7v2TableEntry[]
   */
  function loadEntries() {
    $table_entry         = new CHL7v2TableEntry();
    $table_entry->number = $this->number;
    return $this->_entries = $table_entry->loadMatchingList();
  }

  function countEntries() {
    $table_entry         = new CHL7v2TableEntry();
    $table_entry->number = $this->number;
    return $this->_count_entries = $table_entry->countMatchingList();
  }
}
