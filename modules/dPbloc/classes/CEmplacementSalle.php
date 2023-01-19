<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Exception;
use Ox\Core\CMbObject;

/**
 * Emplacement d'une salle de bloc opératoire sur un plan
 */
class CEmplacementSalle extends CMbObject {

  // DB Table key
  public $emplacement_salle_id;

  // DB Fields
  public $salle_id;
  public $plan_x;
  public $plan_y;
  public $color;
  public $hauteur;
  public $largeur;

  /** @var CSalle */
  public $_ref_salle;

  /**
   * @inheritDoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'emplacement_salle';
    $spec->key   = 'emplacement_salle_id';

    return $spec;
  }

  /**
   * @inheritDoc
   */
  function getProps() {
    $props               = parent::getProps();
    $props["salle_id"]   = "ref notNull class|CSalle back|emplacement_salle";
    $props["plan_x"]     = "num notNull";
    $props["plan_y"]     = "num notNull";
    $props["color"]      = "color default|DDDDDD notNull";
    $props["hauteur"]    = "num notNull default|1 min|1 max|20";
    $props["largeur"]    = "num notNull default|1 min|1 max|20";

    return $props;
  }

  /**
   * @inheritDoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefSalle();
    $this->_view = $this->_ref_salle->nom;
  }

  /**
   * Load the room
   *
   * @return CSalle
   *
   * @throws Exception
   */
  function loadRefSalle() {
    return $this->_ref_salle = $this->loadFwdRef("salle_id", true);
  }
}
