<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use Ox\Core\CStoredObject;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Description
 */
class CPurgeImportedObjects {
  public static $purge_classes = array(
    "CFactureCabinet", "CFactureEtablissement"
  );

  protected $start;
  protected $step;
  protected $import_tag;
  protected $purge_class;
  protected $audit;

  /**
   * CPurgeImportedObjects constructor.
   *
   * @param string $purge_class Class to purge
   * @param string $import_tag  Tag to search for
   * @param int    $start       Start
   * @param int    $step        Number of objects to purge
   * @param int    $audit       Do not purge juste display items
   *
   * @return void
   */
  public function __construct($purge_class, $import_tag, $start = 0, $step = 100, $audit = 1) {
    $this->purge_class = $purge_class;
    $this->import_tag  = $import_tag;
    $this->start       = $start;
    $this->step        = $step;
    $this->audit       = $audit;
  }

  /**
   * Purge items and return their ids
   *
   * @return array()
   */
  public function purge() {
    $external_ids = $this->getExternalIdsToPurge();

    /** @var CStoredObject[] $objects_to_purge */
    $objects_to_purge = CIdSante400::massLoadFwdRef($external_ids, "object_id", $this->purge_class);

    if ($this->audit) {
      return $objects_to_purge;
    }

    return $this->doPurgeObjects($objects_to_purge);
  }

  /**
   * @return array
   */
  protected function getExternalIdsToPurge() {
    $limit = ($this->audit) ? "{$this->start}, {$this->step}" : $this->step;

    $id_sante400               = new CIdSante400();
    $id_sante400->tag          = $this->import_tag;
    $id_sante400->object_class = $this->purge_class;

    $ids_to_purge = $id_sante400->loadMatchingList("id_sante400_id ASC", $limit);

    return $ids_to_purge ?: array();
  }

  /**
   * @param CStoredObject[] $objects Objects to purge
   *
   * @return array
   */
  protected function doPurgeObjects($objects) {
    $purged_ids = array(
      "ok" => array(),
      "errors" => array(),
    );

    /** @var CStoredObject $_obj */
    foreach ($objects as $_obj) {
      $id = $_obj->_id;
      if ($msg = $_obj->purge()) {
        $purged_ids['errors'][$id] = $msg;
      }
      else {
        $purged_ids['ok'][] = $id;
      }
    }

    return $purged_ids;
  }
}
