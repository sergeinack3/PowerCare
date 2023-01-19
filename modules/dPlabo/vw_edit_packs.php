<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkEdit();

$pack_examens_labo_id = CView::get("pack_examens_labo_id", "ref class|CPackExamensLabo", true);

CView::checkin();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("pack_examens_labo_id", $pack_examens_labo_id);

$smarty->display("vw_edit_packs");
