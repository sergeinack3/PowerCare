<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Dossier médical tiers
 */
class CDossierTiers extends CMbObject implements IGroupRelated
{

    /** @var string */
    public const NAME_APPFINE = 'appFine';

  // DB Fields
  public $dossier_tiers_id;

  public $name;
  public $absence_traitement;

  public $object_class;
  public $object_id;
  public $_ref_object;

  // Back references
  /** @var  CAntecedent[] */
  public $_all_antecedents;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'dossier_tiers';
    $spec->key   = 'dossier_tiers_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                       = parent::getProps();
    $props["name"]               = "str";
    $props["object_id"]          = "ref notNull class|CStoredObject meta|object_class back|dossiers_tiers";
    $props["object_class"]       = "enum list|CPatient|CSejour";
    $props["absence_traitement"] = "bool";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function getPerm($permType) {
    $basePerm = CModule::getCanDo('soins')->edit ||
      CModule::getCanDo('dPurgences')->edit ||
      CModule::getCanDo('dPcabinet')->edit ||
      CModule::getCanDo('dPbloc')->edit ||
      CModule::getCanDo('dPplanningOp')->edit;

    return $basePerm && parent::getPerm($permType);
  }

  /**
   * @inheritdoc
   */
  function loadRefObject() {
    if (!$this->object_class) {
      return;
    }
    $this->_ref_object = new $this->object_class;
    $this->_ref_object->load($this->object_id);
  }

  /**
   * Chargement des antécédents du dossier
   *
   * @return CStoredObject[]|null
   * @throws \Exception
   */
  function loadRefsAntecedents() {
    return $this->_all_antecedents = $this->loadBackRefs("antecedents_tiers");
  }

  /**
   * @param CStoredObject $object
   * @deprecated
   * @todo redefine meta raf
   * @return void
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @param bool $cache
   * @deprecated
   * @todo redefine meta raf
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }

    /**
     * @return CGroups|null
     */
    public function loadRelGroup(): ?CGroups
    {
        $this->loadRefObject();
        if ($this->_ref_object instanceof IGroupRelated) {
            return $this->_ref_object->loadRelGroup();
        }

        return null;
    }
}
