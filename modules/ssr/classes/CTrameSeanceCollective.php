<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CFunctions;

/**
 * Trame de planning collectif
 */
class CTrameSeanceCollective extends CMbObject {
  // DB Fields
  public $trame_id;
  // References
  public $group_id;
  public $function_id;

  public $type;
  public $nom;

  public $_ref_plages;
  /** @var CFunctions */
  public $_ref_function;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "trame_seance_collective";
    $spec->key   = "trame_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props["group_id"]    = "ref notNull class|CGroups back|trame_ssr_etab";
    $props["function_id"] = "ref notNull class|CFunctions back|trame_ssr_function";
    $props["type"]        = "enum notNull list|ssr|psy";
    $props["nom"]         = "str notNull";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->nom;
  }

  /**
   * Charge la fonction
   *
   * @return CFunctions
   */
  function loadRefFunction() {
    return $this->_ref_function = $this->loadFwdRef("function_id");
  }

}