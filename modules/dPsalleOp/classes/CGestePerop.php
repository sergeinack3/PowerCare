<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Incident / évenement per-opératoire
 */
class CGestePerop extends CMbObject {
  public $geste_perop_id;
  public $libelle;
  public $description;

  // DB References
  public $group_id;
  public $function_id;
  public $user_id;
  public $categorie_id;
  public $incident;
  public $antecedent_code_cim;
  public $actif;

  public $_datetime;
  public $_view_complet;

  /** @var CGroups */
  public $_ref_group;
  /** @var CFunctions */
  public $_ref_function;
  /** @var CMediusers */
  public $_ref_user;
  /** @var CAnesthPeropCategorie */
  public $_ref_categorie;
  /** @var CFile */
  public $_ref_file;
  /** @var CGestePeropPrecision[] */
  public $_ref_precisions;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec               = parent::getSpec();
    $spec->table        = 'geste_perop';
    $spec->key          = 'geste_perop_id';
    $spec->xor["owner"] = array("user_id", "function_id", "group_id");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                        = parent::getProps();
    $props["group_id"]            = "ref class|CGroups back|gestes_perop";
    $props["function_id"]         = "ref class|CFunctions back|gestes_perop";
    $props["user_id"]             = "ref class|CMediusers back|gestes_perop";
    $props["categorie_id"]        = "ref class|CAnesthPeropCategorie back|gestes_perop";
    $props["libelle"]             = "str notNull";
    $props["description"]         = "text helped";
    $props["incident"]            = "bool default|0";
    $props["antecedent_code_cim"] = "str";
    $props["actif"]               = "bool default|1";

    $props["_datetime"] = "dateTime";

    // References
    $props["_ref_file"] = "ref class|CFile";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->libelle;
  }

  /**
   * Load the CGroups object
   *
   * @return CGroups
   * @throws Exception
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * Load the CFunctions object
   *
   * @return CFunctions
   * @throws Exception
   */
  function loadRefFunction() {
    return $this->_ref_function = $this->loadFwdRef("function_id", true);
  }

  /**
   * Load the CMediusers object
   *
   * @return CMediusers
   * @throws Exception
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }

  /**
   * Load the CAnesthPeropCategorie object
   *
   * @return CAnesthPeropCategorie
   * @throws Exception
   */
  function loadRefCategory() {
    return $this->_ref_categorie = $this->loadFwdRef("categorie_id", true);
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
   * Get gesture perop's precisions
   *
   * @return CGestePeropPrecision[]
   * @throws Exception
   */
  function loadRefPrecisions() {
    return $this->_ref_precisions = $this->loadBackRefs("geste_perop_precisions", "libelle", null, null, null, null, "", array("actif" => " = '1'"));
  }

  /**
   * Load object by chapter
   *
   * @param string $field Field
   * @param string $value Value
   *
   * @return array
   * @throws Exception
   */
  function loadGestesByChapitre($field, $value) {
    $geste_by_chapitre = array();
    $where              = array();

    $where[$field]  = " = '$value'";
    $where["actif"] = " = '1'";
    $gestes        = $this->loadList($where);

    $categories = CStoredObject::massLoadFwdRef($gestes, "categorie_id");
    CStoredObject::massLoadFwdRef($categories, "chapitre_id");

    foreach ($gestes as $_geste) {
      $categorie = $_geste->loadRefCategory();
      $chapitre  = $categorie->loadRefChapitre();

      $chapitre_name  = $chapitre->_id ? $chapitre->_view : CAppUI::tr("common-No chapter");
      $categorie_name = $categorie->_id ? $categorie->_view : CAppUI::tr("common-No category");
      $geste_by_chapitre[$chapitre_name][$categorie_name][] = $_geste;
    }

    ksort($geste_by_chapitre);

    foreach ($geste_by_chapitre as $_chapitre_key => $_chapitre) {
      ksort($geste_by_chapitre[$_chapitre_key]);
    }

    return $geste_by_chapitre;
  }
}
