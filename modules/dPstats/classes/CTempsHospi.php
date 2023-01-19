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
 * Class CTempsHospi
 *
 * Classe de mining des temps d'hospitalisation
 *
 * @todo Passer au mining frameworké
 */
class CTempsHospi extends CMbObject {
  // DB Table key
  public $temps_hospi_id;

  // DB Fields
  public $praticien_id;
  public $type;
  public $ccam;
  public $nb_sejour;
  public $duree_moy;
  public $duree_ecart;

  /** @var CMediusers */
  public $_ref_praticien;

  // Derived Fields
  public $_codes;

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_codes = explode("|", strtoupper($this->ccam));
  }

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'temps_hospi';
    $spec->key   = 'temps_hospi_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs                 = parent::getProps();
    $specs["praticien_id"] = "ref notNull class|CMediusers back|temps_hospi";
    $specs["type"]         = "enum notNull list|comp|ambu|seances|ssr|psy";
    $specs["nb_sejour"]    = "num pos";
    $specs["duree_moy"]    = "currency pos";
    $specs["duree_ecart"]  = "currency pos";
    $specs["ccam"]         = "str";

    return $specs;
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
    return $this->_ref_praticien = $this->loadFwdRef("praticien_id", 1);
  }

  /**
   * Durée moyenne d'hospitlisation en jours
   *
   * @param int          $praticien_id Praticien concerné
   * @param array|string $ccam         Code CCAM concerné
   * @param string       $type         Type de séjour concerné
   *
   * @return int|bool Durée en jours, 0 si aucun séjour, false si temps non calculé
   */
  static function getTime($praticien_id = 0, $ccam = null, $type = null) {
    $where                 = array();
    $total                 = array();
    $total["duree_somme"]  = 0;
    $total["nbSejours"]    = 0;
    $where["praticien_id"] = "= '$praticien_id'";
    if ($type) {
      $where["type"] = "= '$type'";
    }

    if (is_array($ccam)) {
      foreach ($ccam as $code) {
        $where[] = "ccam LIKE '%" . strtoupper($code) . "%'";
      }
    }
    elseif ($ccam) {
      $where["ccam"] = "LIKE '%" . strtoupper($ccam) . "%'";
    }

    $temp = new CTempsHospi;
    if (null == $liste = $temp->loadList($where)) {
      return false;
    }

    foreach ($liste as $temps) {
      $total["nbSejours"]   += $temps->nb_sejour;
      $total["duree_somme"] += $temps->nb_sejour * $temps->duree_moy;
    }

    if ($total["nbSejours"]) {
      $time = $total["duree_somme"] / $total["nbSejours"];
    }
    else {
      $time = 0;
    }

    return $time;
  }
}
