<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbObject;

/**
 * Classe gérant les libellés opératoires
 */
class CLibelleOp extends CMbObject {
  // DB Table key
  public $libelleop_id;

  // DB fields
  public $group_id;
  public $statut;
  public $nom;
  public $date_debut;
  public $date_fin;
  public $services;
  public $mots_cles;
  public $numero;
  public $version;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = 'libelleop';
    $spec->key    = 'libelleop_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["group_id"]    = "ref notNull class|CGroups autocomplete|text back|libelles_op";
    $props["statut"]      = "enum list|valide|no_valide|indefini";
    $props["nom"]         = "str notNull";
    $props["date_debut"]  = "dateTime";
    $props["date_fin"]    = "dateTime";
    $props["services"]    = "str";
    $props["mots_cles"]   = "str";
    $props["numero"]      = "num notNull";
    $props["version"]     = "num default|1";
    return $props;
  }

  /**
   * updateFormFields
   *
   * @return void
   **/
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  /**
   * @see parent::check()
   */
  function check(){
    return parent::check();
  }

}
