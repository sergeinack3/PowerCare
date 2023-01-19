<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Purge;

use Ox\Core\CRequest;
use Ox\Core\CStoredObject;

/**
 * Description
 */
class COperationPurger extends CObjectPurger {
  protected $class_name = "COperation";

  /**
   * @inheritdoc
   */
  protected function getWhere() {
    return array(
      "F.file_id IS NULL",
      "C.compte_rendu_id IS NULL",
      "FS.file_id IS NULL",
      "CS.compte_rendu_id IS NULL",
      "CO.consultation_id IS NULL",
    );
  }

  /**
   * @inheritdoc
   */
  protected function getLJoin() {
    return array(
      "files_mediboard F ON (F.object_class = 'COperation' AND F.object_id = operation_id)",
      "compte_rendu C ON (C.object_class = 'COperation' AND C.object_id = operation_id)",
      "sejour S ON (`operations`.sejour_id = S.sejour_id)",
      "files_mediboard FS ON (FS.object_class = 'CSejour' AND FS.object_id = S.sejour_id)",
      "compte_rendu CS ON (CS.object_class = 'CSejour' AND CS.object_id = S.sejour_id)",
      "consultation CO ON CO.sejour_id = S.sejour_id",
    );
  }

  /**
   * @inheritdoc
   */
  public function countPurgeable() {
    if (!$this->class_name || !class_exists($this->class_name)) {
      return 0;
    }

    /** @var CStoredObject $obj */
    $obj = new $this->class_name();
    $ds = $obj->getDS();

    $query = new CRequest();
    $query->addWhere($this->getWhere());
    $query->addGroup($this->getGroupBy());
    $query->addLJoin($this->getLJoin());
    $query->addSelect("COUNT(DISTINCT `operations`.operation_id) as Total");
    return $ds->loadResult($query->makeSelect($obj)) ?: 0;
  }
}
