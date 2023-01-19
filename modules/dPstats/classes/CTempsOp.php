<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stats;

use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CTempsOp
 *
 * Classe de mining des temps opératoires
 *
 * @todo Passer au mining frameworké
 */
class CTempsOp extends CMbObject {
  // DB Table key
  public $temps_op_id;

  // DB Fields
  public $chir_id;
  public $ccam;
  public $nb_intervention;
  public $estimation;
  public $occup_moy;
  public $occup_ecart;
  public $duree_moy;
  public $duree_ecart;
  public $reveil_moy;
  public $reveil_ecart;

  // Object References
  /** @var  CMediusers */
  public $_ref_praticien;

  // Derived Fields
  public $_codes;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'temps_op';
    $spec->key   = 'temps_op_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs                    = parent::getProps();
    $specs["chir_id"]         = "ref class|CMediusers back|temps_chir";
    $specs["nb_intervention"] = "num pos";
    $specs["estimation"]      = "time";
    $specs["occup_moy"]       = "time";
    $specs["occup_ecart"]     = "time";
    $specs["duree_moy"]       = "time";
    $specs["duree_ecart"]     = "time";
    $specs["reveil_moy"]      = "time";
    $specs["reveil_ecart"]    = "time";
    $specs["ccam"]            = "str";

    return $specs;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_codes = explode("|", strtoupper($this->ccam));
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefPraticien();
    $this->_ref_praticien->loadRefFunction();
  }

  /**
   * Chargement du praticien
   *
   * @return CMediusers Le praticien lié
   */
  function loadRefPraticien() {
    return $this->_ref_praticien = $this->loadFwdRef("chir_id", 1);
  }

  /**
   * Durée moyenne d'intervention
   *
   * @param int          $chir_id [optional]
   * @param array|string $ccam    [optional]
   *
   * @return int|bool Durée en minutes, 0 si aucune intervention, false si temps non calculé
   */
  static function getTime($chir_id = 0, $ccam = null) {
    $where                    = array();
    $total                    = array();
    $total["occup_somme"]     = 0;
    $total["nbInterventions"] = 0;
    $where["chir_id"]         = "= '$chir_id'";

    if (is_array($ccam)) {
      foreach ($ccam as $code) {
        $where[] = "ccam LIKE '%" . strtoupper($code) . "%'";
      }
    }
    elseif ($ccam) {
      $where["ccam"] = "LIKE '%" . strtoupper($ccam) . "%'";
    }

    $temp = new CTempsOp;
    if (null == $liste = $temp->loadList($where)) {
      return false;
    }

    foreach ($liste as $temps) {
      $total["nbInterventions"] += $temps->nb_intervention;
      $total["occup_somme"]     += $temps->nb_intervention * strtotime($temps->occup_moy);
    }

    if ($total["nbInterventions"]) {
      $time = $total["occup_somme"] / $total["nbInterventions"];
    }
    else {
      $time = 0;
    }

    return $time;
  }
}
