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
 * Class CTempsPrepa
 *
 * Classe de mining des temps de préparation du patient
 *
 * @todo Passer au mining frameworké
 */
class CTempsPrepa extends CMbObject {
  // DB Table key
  public $temps_prepa_id;

  // DB Fields
  public $chir_id;
  public $nb_prepa;
  public $nb_plages;
  public $duree_moy;
  public $duree_ecart;

  // Object References
  /** @var  CMediusers */
  public $_ref_praticien;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'temps_prepa';
    $spec->key   = 'temps_prepa_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs                = parent::getProps();
    $specs["chir_id"]     = "ref class|CMediusers back|temps_prepa";
    $specs["nb_plages"]   = "num pos";
    $specs["nb_prepa"]    = "num pos";
    $specs["duree_moy"]   = "time";
    $specs["duree_ecart"] = "time";

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
    return $this->_ref_praticien = $this->loadFwdRef("chir_id", 1);
  }
}
