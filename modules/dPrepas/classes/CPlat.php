<?php
/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Repas;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * The CPlat class
 */
class CPlat extends CMbObject {
  // DB Table key
  public $plat_id;

  // DB Fields
  public $group_id;
  public $nom;
  public $type;
  public $typerepas;

  // Object References
  /** @var CTypeRepas */
  public $_ref_typerepas;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'plats';
    $spec->key   = 'plat_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specs              = parent::getProps();
    $specs["nom"]       = "str notNull";
    $specs["group_id"]  = "ref notNull class|CGroups back|plats";
    $specs["type"]      = "enum notNull list|plat1|plat2|plat3|plat4|plat5|boisson|pain";
    $specs["typerepas"] = "ref notNull class|CTypeRepas back|plats";

    return $specs;
  }

  /**
   * @throws \Exception
   */
  function loadRefsFwd() {
    $this->_ref_typerepas = new CTypeRepas;
    $this->_ref_typerepas->load($this->typerepas);
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  //  @todo: refactor this !
  function canDeleteEx() {
    $query = "SELECT COUNT(DISTINCT repas.repas_id) AS number
                    FROM repas WHERE (`$this->type` IS NOT NULL AND `$this->type` = '$this->plat_id')";
    $obj   = null;
    if (!$this->_spec->ds->loadObject($query, $obj)) {
      return $this->_spec->ds->error();
    }
    if ($obj->number) {
      return CAppUI::tr("CMbObject-msg-nodelete-backrefs") . ": " . $obj->number . " repas";
    }

    return null;
  }
}