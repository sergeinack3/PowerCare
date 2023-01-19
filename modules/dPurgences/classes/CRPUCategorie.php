<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;

/**
 * Description
 */
class CRPUCategorie extends CMbObject {
  /** @var integer Primary key */
  public $rpu_categorie_id;

  // DB fields
  public $group_id;
  public $motif;
  public $actif;

  // References
  /** @var CFile */
  public $_ref_icone;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "rpu_categorie";
    $spec->key   = "rpu_categorie_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props             = parent::getProps();
    $props["group_id"] = "ref class|CGroups back|categories_rpu";
    $props["motif"]    = "str notNull";
    $props["actif"]    = "bool default|0";

    // References
    $props["_ref_icone"] = "ref class|CFile";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->motif;
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->_id) {
      $this->group_id = CGroups::get()->_id;
    }

    return parent::store();
  }

  /**
   * Charge l'icône associée à la catégorie
   *
   * @return CFile
   */
  function loadRefIcone() {
    return $this->_ref_icone = $this->loadNamedFile("icone.jpg");
  }
}
