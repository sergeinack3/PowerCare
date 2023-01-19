<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Symfony\Component\Routing\RouterInterface;

/**
 * The CTypeAnesth class
 */
class CTypeAnesth extends CMbObject {
  /** @var string */
  const RESOURCE_TYPE = "typeAnesth";

  // DB Table key
  public $type_anesth_id;

  // DB Fields
  public $name;
  public $ext_doc;
  public $actif;
  public $group_id;
  public $duree_postop;

  // References
  public $_count_operations;

  /** @var CGroups */
  public $_ref_group;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'type_anesth';
    $spec->key   = 'type_anesth_id';

    return $spec;
  }

  /**
   * @inheritDoc
   */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('planning_typeanesth', ["type_anesth_id" => $this->_id]);
    }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["name"]         = "str notNull fieldset|default";
    $props["ext_doc"]      = "enum list|1|2|3|4|5|6";
    $props["actif"]        = "bool notNull default|1";
    $props["group_id"]     = "ref class|CGroups back|type_anesth_group";
    $props["duree_postop"] = "time";

    return $props;
  }

  /**
   * Load ref Group
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->name;
  }

  /**
   * Count operations
   *
   * @return int
   */
  function countOperations() {
    return $this->_count_operations = $this->countBackRefs("operations");
  }

  /**
   * Load list overlay for current group
   *
   * @param array  $where   Tableau de clauses WHERE MYSQL
   * @param string $order   paramètre ORDER SQL
   * @param null   $limit   paramètre LIMIT SQL
   * @param null   $groupby paramètre GROUP BY SQL
   * @param array  $ljoin   Tableau de clauses LEFT JOIN SQL
   *
   * @return self[]
   */
  function loadGroupList($where = array(), $order = 'name', $limit = null, $groupby = null, $ljoin = array()) {
    // Filtre sur l'établissement
    $where[] = "group_id = '" . CGroups::loadCurrent()->_id . "' OR group_id IS NULL";

    return $this->loadListWithPerms(PERM_READ, $where, $order, $limit, $groupby, $ljoin);
  }
}
