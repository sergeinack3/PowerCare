<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;

/**
 * The group schedule category
 */
class CCategorieGroupePatient extends CMbObject {
  // DB Fields
  public $categorie_groupe_patient_id;

  // References
  public $group_id;
  public $function_id;

  public $type;
  public $nom;

  /** @var CPlageGroupePatient[] */
  public $_ref_plages_groupe;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "categorie_groupe_patient";
    $spec->key   = "categorie_groupe_patient_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props             = parent::getProps();
    $props["group_id"] = "ref notNull class|CGroups back|groupe_patient_categories";
    $props["type"]     = "enum notNull list|ssr|psy";
    $props["nom"]      = "str notNull";

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
   * Load patient group range
   *
   * @return CPlageGroupePatient[]
   * @throws Exception
   */
  function loadRefPlagesGroupe() {
    return $this->_ref_plages_groupe = $this->loadBackRefs("plages_groupe_ssr");
  }

    /**
     * @param null $class
     * @param bool $only_notNull
     *
     * @return CCategorieGroupePatient
     * @throws \Ox\Core\CModelObjectException
     */
  public static function getSampleObject($class = null, bool $only_notNull = false): CModelObject
  {
      /** @var CCategorieGroupePatient $categorie_groupe */
      $categorie_groupe = parent::getSampleObject($class, $only_notNull);

      $categorie_groupe->nom = "CCategorieGroupePatient-" . random_int(1, 1000);

      return $categorie_groupe;
  }
}
