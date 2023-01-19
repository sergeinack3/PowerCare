<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\FieldSpecs\CColorSpec;
use Ox\Mediboard\Etablissement\CGroups;
use Symfony\Component\Routing\RouterInterface;

/**
 * Classe CChargePriceIndicator
 *
 * Table type d'activité, mode de traitement
 */
class CChargePriceIndicator extends CMbObject {
  /** @var string */
  const RESOURCE_TYPE = 'modeTraitement';

  // DB Table key
  public $charge_price_indicator_id;

  // DB Table key
  public $code;
  public $type;
  public $type_pec;
  public $group_id;
  public $libelle;
  public $color;
  public $actif;
  public $hospit_de_jour;
  public $_font_color;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'charge_price_indicator';
    $spec->key   = 'charge_price_indicator_id';
    return $spec;
  }

  /**
   * @inheritDoc
   */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('planning_modetraitement', ["charge_price_indicator_id" => $this->_id]);
    }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["code"]     = "str notNull fieldset|default";
    $sejour = new CSejour();
    $props["type"]     = $sejour->_props["type"];
    $props["type_pec"] = $sejour->getPropsWitouthFieldset("type_pec");
    $props["color"]    = "color default|ffffff notNull";
    $props["group_id"] = "ref notNull class|CGroups back|charges";
    $props["libelle"]  = "str fieldset|default";
    $props["actif"]    = "bool default|0";
    $props["hospit_de_jour"] = "enum list|0|1|";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view      = $this->libelle ? $this->libelle : $this->code;
    $this->_shortview = $this->code;

    $this->_font_color = CColorSpec::get_text_color($this->color) > 130 ? '000000' : "ffffff";
  }

  static function getList($type = null, $group_id = null) {
    $cpi_list = array();
    $group = CGroups::loadCurrent();

    if (CAppUI::conf("dPplanningOp CSejour use_charge_price_indicator", $group) != "no") {
      $cpi = new CChargePriceIndicator();
      $cpi->group_id = $group_id ? : $group->_id;
      $cpi->type     = $type;
      $cpi->actif    = 1;

      $cpi_list = $cpi->loadMatchingList("libelle");
    }

    return $cpi_list;
  }
}
