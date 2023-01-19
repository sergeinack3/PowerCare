<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi;

use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Atih\CRSS;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class CTraitementDossier extends CMbObject {
  public $traitement_dossier_id;

  // DB fields
  public $traitement;
  public $validate;
  public $GHS;
  public $rss_id;
  public $sejour_id;
  public $dim_id;

  /** @var CRSS */
  public $_ref_rss;

  /** @var CMediusers */
  public $_ref_dim;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "traitement_dossier";
    $spec->key   = "traitement_dossier_id";
    $spec->uniques ["dossier"] = array("sejour_id", "rss_id");

    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    static $atih = null;

    if ($atih === null) {
      $atih = CModule::getActive("atih");
    }

    $props = parent::getProps();

    if (class_exists("CRSS")) {
      $props["rss_id"]     = "ref class|CRSS back|traitement_dossier cascade";
    }

    $props["sejour_id"]  = "ref class|CSejour back|traitement_dossier";
    $props["traitement"] = "dateTime";
    $props["validate"]   = "dateTime";
    $props["GHS"]        = "str";
    $props["dim_id"]     = "ref class|CMediusers back|dim";

    return $props;
  }

  /**
   * Charge le DIM ayant validé le groupage.
   *
   * @return CMediusers
   */
  function loadRefDim() {
    return $this->_ref_dim = $this->loadFwdRef("dim_id");
  }
}
