<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Purge;

/**
 * Description
 */
class CSejourPurger extends CObjectPurger {
  protected $class_name = "CSejour";

  /**
   * @inheritdoc
   */
  protected function getWhere() {
    return array(
      "F.file_id IS NULL",
      "CR.compte_rendu_id IS NULL",
      "C.consultation_id IS NULL",
      "O.operation_id IS NULL",
    );
  }

  /**
   * @inheritdoc
   */
  protected function getLJoin() {
    return array(
      "files_mediboard F ON (F.object_class = 'CSejour' AND F.object_id = `sejour`.sejour_id)",
      "compte_rendu CR ON (CR.object_class = 'CSejour' AND CR.object_id = `sejour`.sejour_id)",
      "consultation C ON C.sejour_id = `sejour`.sejour_id",
      "operations O ON O.sejour_id = `sejour`.sejour_id",
    );
  }
}
