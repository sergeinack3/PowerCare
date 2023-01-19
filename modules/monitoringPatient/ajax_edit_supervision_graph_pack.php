<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\MonitoringPatient\CSupervisionGraphPack;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkAdmin();
$supervision_graph_pack_id = CView::get("supervision_graph_pack_id", "ref class|CSupervisionGraphPack");
CView::checkin();

$pack = new CSupervisionGraphPack();
$pack->load($supervision_graph_pack_id);
$pack->loadRefsNotes();
$pack->getTimingFields();

// Liste des types d'anesthésie
$anesthesia_type  = new CTypeAnesth();
$anesthesia_types = $anesthesia_type->loadGroupList();

$smarty = new CSmartyDP();
$smarty->assign("pack"            , $pack);
$smarty->assign('anesthesia_types', $anesthesia_types);
$smarty->display("inc_edit_supervision_graph_pack");
