<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\OpenData;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 */
class CImportConflict extends CMbObject {
  /** @var integer Primary key */
  public $import_conflict_id;

  public $field;
  public $value;
  public $audit;
  public $file_version;
  public $import_tag;
  public $ignore;

  public $object_class;
  public $object_id;
  public $_ref_object;

  public $_ignore;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->loggable = false;
    $spec->table    = "import_conflict";
    $spec->key      = "import_conflict_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["field"]        = "str notNull";
    $props["value"]        = "text markdown";
    $props["audit"]        = "bool default|1";
    $props["file_version"] = "str";
    $props["import_tag"]   = "str";
    $props["ignore"]       = "bool default|0";
    $props["object_id"]    = "ref notNull class|CMbObject meta|object_class back|import_conflict cascade";
    $props["object_class"] = "str notNull class show|0";

    $props["_ignore"]       = "set list|0|1";

    return $props;
  }

  /**
   * Get all the conflicts for a medecin
   *
   * @param int|array $medecin_id Medecin id to get conflicts for
   * @param string    $tag        Tag to search for
   *
   * @return CImportConflict[]|array
   * @throws Exception
   */
  static function getConflictsForMedecin($medecin_id, $tag = null, $audit = false) {
    $ds = CSQLDataSource::get('std');

    $ids = (is_array($medecin_id)) ? $medecin_id : explode('|', $medecin_id);

    $query = new CRequest();
    $query->addSelect('import_conflict_id');
    $query->addTable('import_conflict');
    $where =  array(
      'object_id' => $ds->prepareIn($ids),
    );

    if ($tag) {
      $where['import_tag'] = $ds->prepare("= ?", $tag);
    }
    else {
      $where['import_tag'] = 'IS NULL';
    }

    if ($audit) {
      $where['audit'] = "= '1'";
    }
    else {
      $where['audit'] = "= '0'";
    }

    $query->addWhere($where);

    $conflicts_ids = $ds->loadList($query->makeSelect());
    if ($conflicts_ids) {
      $conflicts_ids = CMbArray::pluck($conflicts_ids, 'import_conflict_id');
    }

    return $conflicts_ids;
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
