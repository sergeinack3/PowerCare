<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CMbObject;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Fiche d'autonomie saisie en amont de l'hospitalisation SSR pour déterminer l'acceptation ou récusion du dossier
 */
class CFicheAutonomie extends CMbObject {
  // DB Table key
  public $fiche_autonomie_id;

  // DB Fields
  public $sejour_id;
  public $alimentation;
  public $toilette;
  public $habillage_haut;
  public $habillage_bas;
  public $toilettes;
  public $utilisation_toilette;
  public $transfert_lit;
  public $locomotion;
  public $locomotion_materiel;
  public $escalier;
  public $pansement;
  public $escarre;
  public $soins_cutanes;
  public $comprehension;
  public $expression;
  public $memoire;
  public $resolution_pb;
  public $antecedents;
  public $traitements;
  public $etat_psychique;
  public $devenir_envisage;

  // Object References
  /** @var CSejour */
  public $_ref_sejour;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec                       = parent::getSpec();
    $spec->table                = 'fiche_autonomie';
    $spec->key                  = 'fiche_autonomie_id';
    $spec->uniques["sejour_id"] = array("sejour_id");

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                         = parent::getProps();
    $props["sejour_id"]            = "ref notNull class|CSejour cascade back|fiche_autonomie";
    $props["alimentation"]         = "enum notNull list|autonome|partielle|totale";
    $props["toilette"]             = "enum notNull list|autonome|partielle|totale";
    $props["habillage_haut"]       = "enum notNull list|autonome|partielle|totale";
    $props["habillage_bas"]        = "enum notNull list|autonome|partielle|totale";
    $props["transfert_lit"]        = "enum notNull list|autonome|partielle|totale";
    $props["locomotion"]           = "enum notNull list|autonome|partielle|totale";
    $props["escalier"]             = "enum notNull list|autonome|partielle|totale";
    $props["toilettes"]            = "enum notNull list|autonome|partielle|totale";
    $props["utilisation_toilette"] = "enum list|sonde|couche|bassin|stomie";
    $props["locomotion_materiel"]  = "enum list|canne|cadre|fauteuil";
    $props["pansement"]            = "bool notNull";
    $props["escarre"]              = "bool notNull";
    $props["soins_cutanes"]        = "text";
    $props["comprehension"]        = "enum notNull list|intacte|alteree";
    $props["expression"]           = "enum notNull list|intacte|alteree";
    $props["memoire"]              = "enum notNull list|intacte|alteree";
    $props["resolution_pb"]        = "enum notNull list|intacte|alteree";
    $props["etat_psychique"]       = "text";
    $props["antecedents"]          = "text";
    $props["traitements"]          = "text";
    $props["devenir_envisage"]     = "text";

    return $props;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadRefSejour();
  }

  /**
   * Charge le séjour associé
   *
   * @return CSejour
   */
  function loadRefSejour() {
    $this->_ref_sejour = new CSejour;
    $this->_ref_sejour->load($this->sejour_id);
    $this->_ref_sejour->loadRefsFwd();

    return $this->_ref_sejour;
  }
}
