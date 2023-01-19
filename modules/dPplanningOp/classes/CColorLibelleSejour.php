<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;

/**
 * Associate color to séjour according to libelle
 */
class CColorLibelleSejour extends CMbObject {
  // DB Table key
  public $color_id;

  // DB Fields
  public $libelle;
  public $color;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'color_libelle_sejour';
    $spec->key   = 'color_id';
    $spec->uniques["libelle"] = array("libelle");
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs = parent::getProps();
    $specs["libelle"] = "str notNull";
    $specs["color"]   = "color";
    return $specs;
  }

  static function loadAllFor($libelles) {
    $libelles = array_map("strtoupper", $libelles);

    // Initialisation du tableau
    $colors_by_libelle = array();
    foreach ($libelles as $_libelle) {
      $color = new self;
      $color->libelle = $_libelle;
      $colors_by_libelle[$_libelle] = $color;
    }

    $color = new self;
    $where = array();
    $libelles = array_map("addslashes", $libelles);
    $where["libelle"] = CSQLDataSource::prepareIn($libelles);
    foreach ($color->loadList($where) as $_color) {
      $colors_by_libelle[$_color->libelle] = $_color;
    }

    return $colors_by_libelle;
  }
}
