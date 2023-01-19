<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;

CCanDo::checkEdit();

// Utilisateur sélectionné ou utilisateur courant
$selConsult    = CView::getRefCheckRead("selConsult", "ref class|CConsultation", true);
$view          = CView::get('view', 'enum list|cabinet|oxCabinet default|cabinet');

CView::checkin();

$consult = new CConsultation();
$consult->load($selConsult);

CAccessMedicalData::logAccess($consult);

$consult->loadRefPlageConsult();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consult", $consult);
$smarty->assign("view"   , $view);

$smarty->display("inc_vw_cotation.tpl");
