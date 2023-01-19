<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CConsultationPostNatale;
use Ox\Mediboard\Maternite\CDossierPerinat;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
$dossier_perinat_id = CView::get("dossier_perinat_id", "ref class|CDossierPerinat");
CView::checkin();

$dossier_perinat = new CDossierPerinat();
$dossier_perinat->load($dossier_perinat_id);
$dossier_perinat->loadRefGrossesse()->loadRefParturiente();

$consult_post_natale = new CConsultationPostNatale();

// Liste des consultants
$mediuser        = new CMediusers();
$listConsultants = $mediuser->loadProfessionnelDeSanteByPref(PERM_EDIT);

$smarty = new CSmartyDP();
$smarty->assign("dossier", $dossier_perinat);
$smarty->assign("listConsultants", $listConsultants);
$smarty->assign("consult_post_natale", $consult_post_natale);
$smarty->assign("callback_cs", "");
$smarty->display("vw_edit_consultation_post_natale.tpl");
