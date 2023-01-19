<?php
/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Reservation;

use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;

/**
 * Classe CCommentairePlanning
 * gère les commentaires sous la forme libellé + description dans le planning de réservation
 */
class CCommentairePlanning extends CStoredObject {
  // DB Table key
  public $commentaire_planning_id;

  // DB References
  public $salle_id;

  // DB Fields
  public $libelle;
  public $commentaire;
  public $color;
  public $debut;
  public $fin;

  /**
   * Specs
   *
   * @return CMbObjectSpec
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'commentaire_planning';
    $spec->key   = 'commentaire_planning_id';

    return $spec;
  }

  /**
   * Properties
   *
   * @return array
   */
  function getProps() {
    $specs                = parent::getProps();
    $specs["salle_id"]    = "ref class|CSalle back|commentaires";
    $specs["libelle"]     = "str autocomplete notNull";
    $specs["commentaire"] = "text helped";
    $specs["color"]       = "color default|DDDDDD";
    $specs["debut"]       = "dateTime notNull";
    $specs["fin"]         = "dateTime notNull moreThan|debut";

    return $specs;
  }

  /**
   * updateFormFields
   *
   * @return null
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->libelle;
  }
}
