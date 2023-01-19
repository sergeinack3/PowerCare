<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CMbObject;

/**
 * L'emplacement d'une chambre sur un plan
 */
class CEmplacement extends CMbObject {

  // DB Table key
  public $emplacement_id;

  // DB Fields
  public $chambre_id;
  public $plan_x;
  public $plan_y;
  public $color;
  public $hauteur;
  public $largeur;

  /** @var CChambre */
  public $_ref_chambre;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'emplacement';
    $spec->key   = 'emplacement_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props               = parent::getProps();
    $props["chambre_id"] = "ref notNull class|CChambre back|emplacement";
    $props["plan_x"]     = "num notNull";
    $props["plan_y"]     = "num notNull";
    $props["color"]      = "color default|DDDDDD notNull";
    $props["hauteur"]    = "num notNull default|1 min|1 max|20";
    $props["largeur"]    = "num notNull default|1 min|1 max|20";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefChambre();
    $this->_view = $this->_ref_chambre->nom;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadRefChambre();
  }

  /**
   * Chargement de la chambre concernée par l'emplacement
   *
   * @return $this->_ref_chambre
   **/
  function loadRefChambre() {
    return $this->_ref_chambre = $this->loadFwdRef("chambre_id", true);
  }
}
