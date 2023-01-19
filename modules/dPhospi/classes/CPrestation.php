<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Softway\CSoftwayPrestation;
use Ox\Mediboard\Web100T\CWeb100TPrestation;

/**
 * Type de prestation
 */
class CPrestation extends CMbObject {
  // DB Table key
  public $prestation_id;

  // DB references
  public $group_id;

  // DB fields
  public $nom;
  public $code;
  public $description;

  /** @var CGroups */
  public $_ref_group;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'prestation';
    $spec->key   = 'prestation_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs                = parent::getProps();
    $specs["group_id"]    = "ref notNull class|CGroups back|prestations";
    $specs["nom"]         = "str notNull seekable";
    $specs["code"]        = "str maxLength|12 seekable";
    $specs["description"] = "text confidential seekable";

    return $specs;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  /**
   * Charge l'établissement
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefGroup();
  }

  /**
   * Niveaux de prestations pour l'établissement courant
   *
   * @return self[]
   */
  static function loadCurrentList() {
    $prestation           = new self();
    $prestation->group_id = CGroups::loadCurrent()->_id;

    return $prestation->loadMatchingList("nom");
  }

  /**
   * Generate flow
   *
   * @param CSejour $sejour Admit
   *
   * @return string flow
   */
  static function generateFlow(CSejour $sejour) {
    switch (CAppUI::conf("dPhospi prestations systeme_prestations_tiers", "CGroups-$sejour->group_id")) {
      case "web100T":
        $web100t_presta = new CWeb100TPrestation();

        return $web100t_presta->generateFlow($sejour);

      case "softway":
        $softway_presta = new CSoftwayPrestation();

        return $softway_presta->generateFlow($sejour);

      default:
        break;
    }
  }

  /**
   * Send prestations
   *
   * @param CSejour $sejour Admit
   * @param string  $flow   Flow
   *
   */
  static function send(CSejour $sejour, $flow) {
    switch (CAppUI::conf("dPhospi prestations systeme_prestations_tiers", "CGroups-$sejour->group_id")) {
      case "web100T":
        $web100t_presta = new CWeb100TPrestation();
        $web100t_presta->sendPresta($sejour, $flow);
        break;

      case "softway":
        $softway_presta = new CSoftwayPrestation();
        $softway_presta->sendPresta($sejour, $flow);

        break;

      default:
        break;
    }
  }
}

