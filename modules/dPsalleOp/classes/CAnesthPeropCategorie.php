<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Catégorie de l'évenement per-opératoire
 */
class CAnesthPeropCategorie extends CMbObject {
  public $anesth_perop_categorie_id;

  // DB fields
  public $group_id;
  public $chapitre_id;
  public $libelle;
  public $description;
  public $actif;

  /** @var CFile */
  public $_ref_file;
  /** @var CGroups */
  public $_ref_group;
  /** @var CGestePerop[] */
  public $_ref_gestes_perop;
  /** @var CAnesthPeropChapitre */
  public $_ref_chapitre;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'anesth_perop_categorie';
    $spec->key   = 'anesth_perop_categorie_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                = parent::getProps();
    $props["group_id"]    = "ref notNull class|CGroups back|anesth_perop_categories";
    $props["chapitre_id"] = "ref class|CAnesthPeropChapitre back|anesth_perop_categories";
    $props["libelle"]     = "str notNull";
    $props["description"] = "text";
    $props["actif"]       = "bool default|1";

    // References
    $props["_ref_file"] = "ref class|CFile";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = "$this->libelle";
  }

  /**
   * Load files for object
   *
   * @return CFile|CStoredObject
   * @throws Exception
   */
  function loadRefFile() {
    return $this->_ref_file = $this->loadUniqueBackRef("files", "file_name");
  }

  /**
   * Load gestes Perop
   *
   * @param array $where          Clause where
   * @param int   $limit_view     Limit view
   * @param int   $see_all_gestes See all gestures
   *
   * @return CGestePerop[]
   * @throws Exception
   */
  function loadRefsGestesPerop($where = array(), $limit_view = 0, $see_all_gestes = 0) {
    if ($limit_view) {
      $group    = CGroups::loadCurrent();
      $user      = CMediusers::get();

      if ($see_all_gestes) {
        $users     = $user->loadUsers();
        $functions = $group->loadFunctions();

        $where[] = "user_id " .CSQLDataSource::prepareIn(array_keys($users)). " OR function_id " .CSQLDataSource::prepareIn(array_keys($functions)). " OR group_id = '$group->_id'";
      }
      else {
        $function = $user->loadRefFunction();

        $where[] = "user_id = '$user->_id' OR function_id = '$function->_id' OR group_id = '$group->_id'";
      }
    }

    $where[] = "actif = '1'";

    return $this->_ref_gestes_perop = $this->loadBackRefs("gestes_perop", "libelle ASC", null, null, null, null, "", $where);
  }

  /**
   * Load group forward reference
   *
   * @return CGroups
   * @throws Exception
   */
  function loadRefGroup() {
    /** @var CGroups */
    return $this->_ref_group = $this->loadFwdRef("group_id", 1);
  }

  /**
   * Load perop chapter forward reference
   *
   * @return CAnesthPeropChapitre
   * @throws Exception
   */
  function loadRefChapitre() {
    return $this->_ref_chapitre = $this->loadFwdRef("chapitre_id", 1);
  }
}
