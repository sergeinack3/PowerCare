<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;

CCanDo::checkEdit();

$salle_id = CView::post('salle_id', 'ref class|CSalle');
$deb      = CView::post('deb', 'dateTime notNull');
$fin      = CView::post('fin', 'dateTime notNull');
$del      = CView::post('del', 'bool default|0');

if ($del == 0) {
  $salle_id = CValue::post("salle_id");
  $plage = new CPlageOp;

  $where = array();

  $where["salle_id"] = "= '$salle_id'";
  $where[]           = "CONCAT(date, ' ', debut) BETWEEN '$deb' AND '$fin' OR CONCAT(date, ' ', fin) BETWEEN '$deb' AND '$fin'";

  $plages = $plage->loadList($where);

  foreach ($plages as $_plage) {
    if ($_plage->countBackRefs("operations") == 0) {
      $_debut = $_plage->date . ' ' . $_plage->debut;
      $_fin = $_plage->date . ' ' . $_plage->fin;

      if ($_debut < $deb || $_fin > $fin) {
        if ($_debut < $deb && $_fin <= $fin) {
          $_plage->fin = CMbDT::time('-1 MINUTES', $deb);
        }
        elseif ($_fin > $fin && $_debut >= $deb) {
          $_plage->debut = CMbDT::time('+1 MINUTES', $fin);
        }

        if ($msg = $_plage->store()) {
          CAppUI::setMsg($msg);
        }
        else {
          CAppUI::setMsg(CAppUI::tr("CPlageOp-msg-modify"));
        }
      }
      else {
        if ($msg = $_plage->delete()) {
          CAppUI::setMsg($msg);
        }
        else {
          CAppUI::setMsg(CAppUI::tr("CPlageOp-msg-delete"));
        }
      }
    }
  }
}

$do = new CDoObjectAddEdit("CBlocage");
$do->doIt();
