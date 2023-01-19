<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;

/**
 * Actes prestations SSR
 */
class CActePrestationSSR extends CActeSSR {
  public $acte_prestation_id;

  // DB Fields
  public $type;
  public $commentaire;

  /** @var CPrestaSSR */
  public $_ref_presta_ssr;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'acte_prestation';
    $spec->key   = 'acte_prestation_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props["sejour_id"]  .= " back|actes_presta_ssr";
    $props["administration_id"] .= " back|actes_presta_ssr";
    $props["evenement_ssr_id"] .= " back|prestas_ssr";
    $props["type"]        = "enum list|presta_ssr";
    $props["code"]        = "str show|0";
    $props["commentaire"] = "text";

    return $props;
  }

  /**
   * Charge la prestation SSR associée
   *
   * @return CPrestaSSR
   */
  function loadRefPrestationSSR() {
    $presta      = CPrestaSSR::get($this->code);
    $this->_view = $presta->libelle;

    return $this->_ref_presta_ssr = $presta;
  }

  /**
   * Charge en masse la prestation SSR pour une collection d'actes
   *
   * @param self[] $actes Liste d'actes
   *
   * @return CPrestaSSR[]
   */
  static function massLoadRefPrestationSSR($actes = array()) {
    if (!count($actes)) {
      return;
    }

    $codes = array_unique(CMbArray::pluck($actes, "code"));

    $presta  = new CPrestaSSR();
    $prestas = $presta->loadList(array("code" => CSQLDataSource::prepareIn($codes)));

    $codes_presta = CMbArray::pluck($prestas, "code");

    foreach ($actes as $_acte) {
      if (false !== $key = array_search($_acte->code, $codes_presta)) {
        $_acte->_ref_presta_ssr = $prestas[$key];
      }
    }

    return $prestas;
  }

  /**
   * Charge le type d'objet associé au code et au type
   *
   * @return void
   */
  function loadRefCodage() {
    $this->loadRefPrestationSSR();
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->loadRefCodage();
  }
}
