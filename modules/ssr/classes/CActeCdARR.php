<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

/**
 * Actes SSR de la nomenclature CdARR
 */
class CActeCdARR extends CActeSSR {
  public $acte_cdarr_id;

  /** @var CActiviteCdARR */
  public $_ref_activite_cdarr;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'acte_cdarr';
    $spec->key   = 'acte_cdarr_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props         = parent::getProps();
    $props["sejour_id"] .= " back|actes_cdarr";
    $props["administration_id"] .= " back|actes_cdarr";
    $props["evenement_ssr_id"] .= " back|actes_cdarr";
    $props["code"] = "str notNull length|4 show|0";

    return $props;
  }

  /**
   * Chargement de l'activité associée
   *
   * @return CActiviteCdARR
   */
  function loadRefActiviteCdARR() {
    $activite = CActiviteCdARR::get($this->code);
    $activite->loadRefTypeActivite();

    return $this->_ref_activite_cdarr = $activite;
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->loadRefActiviteCdARR();
  }
}
