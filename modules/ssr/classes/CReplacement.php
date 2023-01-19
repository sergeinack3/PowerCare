<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbRange;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Le remplacement permet d'associer un nouveau référent lorsque le référent d'un sejour SSR est en congés
 */
class CReplacement extends CMbObject {
  // DB Table key
  public $replacement_id;

  // DB Fields
  public $sejour_id;
  public $conge_id;
  public $replacer_id;

  public $deb;
  public $fin;

  /** @var CSejour */
  public $_ref_sejour;
  /** @var CPlageConge */
  public $_ref_conge;
  /** @var CMediusers */
  public $_ref_replacer;

  // Distant fields
  public $_min_deb;
  public $_max_fin;

  // Distant collections
  /** @var CPlageConge[] */
  public $_ref_replacer_conges;
  /** @var CMediusers[] */
  public $_ref_guessed_replacers;
  /** @var array */
  public $_ref_replacement_fragments;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec                    = parent::getSpec();
    $spec->table             = "replacement";
    $spec->key               = "replacement_id";
    $spec->uniques["unique"] = array("sejour_id", "conge_id");

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    // DB Fields
    $props["sejour_id"]   = "ref notNull class|CSejour back|replacements";
    $props["conge_id"]    = "ref notNull class|CPlageConge back|replacement";
    $props["replacer_id"] = "ref notNull class|CMediusers back|replacements";

    $props["deb"] = "date";
    $props["fin"] = "date";

    // Distant Fields
    $props["_min_deb"] = "date";
    $props["_max_fin"] = "date";

    return $props;
  }

  /**
   * @see parent::check()
   */
  function check() {
    if ($msg = parent::check()) {
      return $msg;
    }

    $this->completeField("conge_id", "replacer_id");
    $this->loadRefConge();

    if ($this->_ref_conge->user_id == $this->replacer_id) {
      return "$this->_class-failed-same_user";
    }

    return null;
  }

  /**
   * @see parent::store()
   */
  function store() {
    if ($msg = parent::store()) {
      return $msg;
    }

    // Lors de la creation du remplacement, on reaffecte les evenements du kine principal
    $this->completeField("sejour_id", "conge_id");
    $conge      = $this->loadRefConge();
    $sejour     = $this->loadRefSejour();
    $bilan      = $sejour->loadRefBilanSSR();
    $technicien = $bilan->loadRefTechnicien();
    $kine_id    = $technicien->kine_id;

    $date_debut             = $conge->date_debut;
    $date_fin               = CMbDT::date("+1 DAY", $conge->date_fin);
    $evenement_ssr          = new CEvenementSSR();
    $where                  = array();
    $where["therapeute_id"] = " = '$kine_id'";
    $where["sejour_id"]     = " = '$this->sejour_id'";
    $where["debut"]         = "BETWEEN '$date_debut' AND '$date_fin'";

    /** @var CEvenementSSR[] $evenements */
    $evenements = $evenement_ssr->loadList($where);

    foreach ($evenements as $_evenement) {
      $_evenement->therapeute_id = $this->replacer_id;
      if ($msg = $_evenement->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
    }

    return null;
  }

  /**
   * @see parent::delete()
   */
  function delete() {
    // Lors de la suppression du remplacant, on reaffecte les evenements au kine principal
    $this->completeField("sejour_id", "conge_id", "replacer_id");
    $conge  = $this->loadRefConge();
    $sejour = $this->loadRefSejour();
    $bilan  = $sejour->loadRefBilanSSR();
    $bilan->loadRefTechnicien();

    $date_debut             = $conge->date_debut;
    $date_fin               = CMbDT::date("+1 DAY", $conge->date_fin);
    $evenement_ssr          = new CEvenementSSR();
    $where                  = array();
    $where["therapeute_id"] = " = '$this->replacer_id'";
    $where["sejour_id"]     = " = '$this->sejour_id'";
    $where["debut"]         = "BETWEEN '$date_debut' AND '$date_fin'";

    /** @var CEvenementSSR[] $evenements */
    $evenements = $evenement_ssr->loadList($where);

    foreach ($evenements as $_evenement) {
      $_evenement->therapeute_id = $sejour->_ref_bilan_ssr->_ref_technicien->kine_id;
      if ($msg = $_evenement->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
    }

    return parent::delete();
  }

  /**
   * Charge le séjour concerné
   *
   * @return CSejour
   */
  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }

  /**
   * Charge le congé concerné
   *
   * @return CPlageConge
   */
  function loadRefConge() {
    return $this->_ref_conge = $this->loadFwdRef("conge_id", true);
  }

  /**
   * Charge le remplaçant
   *
   * @return CMediusers
   */
  function loadRefReplacer() {
    return $this->_ref_replacer = $this->loadFwdRef("replacer_id", true);
  }

  /**
   * Calcul les bornes min et max des dates concernées par ce remplacement
   *
   * @return void
   */
  function loadDates() {
    $conge  = $this->loadRefConge();
    $sejour = $this->loadRefSejour();

    list($this->_min_deb, $this->_max_fin) = CMbRange::intersection(
      CMbDT::date($sejour->entree), CMbDT::date($sejour->sortie),
      $conge->date_debut, $conge->date_fin
    );
  }

  /**
   * Charge les possibles congés du remplacant pendant le remplacement
   *
   * @return CPlageConge[]
   */
  function checkCongesRemplacer() {
    $this->loadDates();

    $conge = new CPlageConge();

    return $this->_ref_replacer_conges = $conge->loadListForRange($this->replacer_id, $this->_min_deb, $this->_max_fin);
  }

  /**
   * Fragment un remplacement par des congés du remplacant
   *
   * @return array Fragments d'intervales de dates
   */
  function makeFragments() {
    $fragments = array();
    $croppers  = array();
    foreach ($this->_ref_replacer_conges as $_conge) {
      $croppers[] = array("lower" => CMbDT::date("-1 DAY", $_conge->date_debut), "upper" => CMbDT::date("+1 DAY", $_conge->date_fin));
    }

    if (count($this->_ref_replacer_conges) > 0) {
      $fragments = CMbRange::multiCrop(array(array("lower" => $this->deb, "upper" => $this->fin)), $croppers);
      foreach ($fragments as $key => $_fragment) {
        if (!CMbRange::collides($this->_min_deb, $this->_max_fin, $_fragment[0], $_fragment[1])) {
          unset($fragments[$key]);
        }
      }
    }

    return $this->_ref_replacement_fragments = $fragments;
  }

  /**
   * Charge les remplacements d'un utilisateur à une date
   *
   * @param int    $user_id Utilisateur
   * @param string $date    Date
   *
   * @return self[]
   */
  function loadListFor($user_id, $date) {
    $join["plageconge"]               = "replacement.conge_id = plageconge.plage_id";
    $where[]                          = "'$date' BETWEEN plageconge.date_debut AND plageconge.date_fin";
    $where["replacement.replacer_id"] = "= '$user_id'";

    return $this->loadList($where, null, null, null, $join);
  }
}
