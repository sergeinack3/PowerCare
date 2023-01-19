<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CExamNyha;

CCanDo::checkRead();

$consultation_id = CValue::getOrSession("consultation_id");

$where = array("consultation_id" => "= '$consultation_id'");
$exam_nyha = new CExamNyha;
$exam_nyha->loadObject($where);
$exam_nyha->loadRefsNotes();

if (!$exam_nyha->_id) {
  $exam_nyha->consultation_id = $consultation_id;
}

$consultation = $exam_nyha->loadRefConsult();
$consultation->loadRefsFwd();
$consultation->loadRefConsultAnesth();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("exam_nyha" , $exam_nyha);

$smarty->display("exam_nyha.tpl");
