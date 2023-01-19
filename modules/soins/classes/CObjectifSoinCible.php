<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Class CObjectifSoinCible
 */
class CObjectifSoinCible extends CMbObject {
  public $objectif_soin_cible_id;

  // DB Fields
  public $objectif_soin_id;

  public $object_class;
  public $object_id;
  public $_ref_object;

  /** @var CObjectifSoin */
  public $_ref_objectif_soin;

  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'objectif_soin_cible';
    $spec->key   = 'objectif_soin_cible_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                     = parent::getProps();
    $props["objectif_soin_id"] = "ref notNull class|CObjectifSoin cascade back|cibles";
    $props["object_id"]        = "ref class|CMbObject meta|object_class nullify back|objectifs";
    $props["object_class"]     = "enum list|CPrescriptionLineElement|CPrescriptionLineMedicament|CPrescriptionLineComment|CCategoryPrescription|CAdministration|CPrescriptionLineMix show|0";

    return $props;
  }

  function loadRefObjectifSoin() {
    $this->_ref_objectif_soin = $this->loadFwdRef("objectif_soin_id", true);
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
}
