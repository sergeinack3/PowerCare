<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkRead();

CAppUI::requireModuleFile("dPurgences", "redirect_barcode");

// Parametre de tri
$order_way = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$order_col = CView::get("order_col", "str default|_pec_transport", true);
$tri_reconvocation = CView::get("tri_reconvocation", "bool default|0", true);

// Type d'affichage main courante
$selAffichage  = CView::get("selAffichage", "str default|" . CAppUI::conf("dPurgences default_view"), true);
$urgentiste_id = CView::get("urgentiste_id", "ref class|CMediusers", true);
$ccmu          = CView::get("ccmu", "str", true);
$rpu_id        = CView::get("rpu_id", "ref class|CRPU");
$sejour_id     = CView::get("sejour_id", "ref class|CSejour");

// Service en session pour la main courante
$service_id = CView::get("service_id", "ref class|CService", true);

// Type d'affichage UHCD
$uhcd_affichage     = CView::post("uhcd_affichage", "str default|" . CAppUI::conf("dPurgences default_view"), true);
$imagerie_affichage = CView::post("imagerie_affichage", "str default|" . CAppUI::conf("dPurgences default_view"), true);

// Selection de la date
$date = CView::get("date", "date default|now", true);

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$group       = CGroups::get();
$user        = CMediusers::get();
$urgentistes = CAppUI::conf("dPurgences only_prat_responsable") ?
  $user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true) :
  $user->loadListFromType(null, PERM_READ, $group->service_urgences_id, null, true, true);

if ($sejour_id) {
  $rpu            = new CRPU();
  $rpu->sejour_id = $sejour_id;
  $rpu_id         = $rpu->loadMatchingObject();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("services"                            , CService::loadServicesUrgence());
$smarty->assign("group"                               , CGroups::loadCurrent());
$smarty->assign("selAffichage"                        , $selAffichage);
$smarty->assign("service_id"                          , $service_id);
$smarty->assign("uhcd_affichage"                      , $uhcd_affichage);
$smarty->assign("imagerie_affichage"                  , $imagerie_affichage);
$smarty->assign("date"                                , $date);
$smarty->assign("urgentistes"                         , $urgentistes);
$smarty->assign("urgentiste_id"                       , $urgentiste_id);
$smarty->assign("ccmu"                                , $ccmu);
$smarty->assign("rpu_id"                              , $rpu_id);
$smarty->assign("isImedsInstalled"                    , (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));
$smarty->assign("main_courante_refresh_frequency"     , CAppUI::conf("dPurgences main_courante_refresh_frequency"));
$smarty->assign("uhcd_refresh_frequency"              , CAppUI::conf("dPurgences uhcd_refresh_frequency"));
$smarty->assign("imagerie_refresh_frequency"          , CAppUI::conf("dPurgences imagerie_refresh_frequency"));
$smarty->assign("identito_vigilance_refresh_frequency", CAppUI::conf("dPurgences identito_vigilance_refresh_frequency"));
$smarty->assign("avis_maternite_refresh_frequency"    , CAppUI::conf("dPurgences avis_maternite_refresh_frequency"));
$smarty->display("vw_idx_rpu");
