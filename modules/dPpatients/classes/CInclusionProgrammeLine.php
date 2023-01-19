<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbObject;

/**
 * Quand un patient est inclus à programme, il faut permettre sur chaque ligne de prescription de déterminer si elle fait partie du
 * programme
 */
class CInclusionProgrammeLine extends CMbObject {
  // DB Table key
  public $inclusion_programme_line_id;

  // DB fields
  public $inclusion_programme_id;
  public $line_class;
  public $line_id;

  public $_ref_object;

  /** @var CInclusionProgramme */
  public $_ref_inclusion_programme;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'inclusion_programme_line';
    $spec->key   = 'inclusion_programme_line_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                           = parent::getProps();
    $props["inclusion_programme_id"] = "ref notNull class|CInclusionProgramme back|programme_line";
    $props["line_class"]             = "enum list|CPrescriptionLineMedicament|CPrescriptionLineMix|CPrescriptionLineElement|CPrescriptionLineComment";
    $props["line_id"]                = "ref notNull class|CMbObject meta|line_class back|programme_line";

    return $props;
  }

  /**
   * Get the program inclusion
   *
   * @return CInclusionProgramme
   */
  function loadRefInclusionProgramme() {
    return $this->_ref_inclusion_programme = $this->loadFwdRef("inclusion_programme_id", true);
  }

  /**
   * Chargement de l'objet lié à l'inclusion du patient à un programme
   *
   * @return CMbObject
   */
  function loadRefObject() {
    if (!$this->line_class) {
      return;
    }
    $this->_ref_object = new $this->line_class;
    $this->_ref_object->load($this->line_id);

    return $this->_ref_object;
  }
}
