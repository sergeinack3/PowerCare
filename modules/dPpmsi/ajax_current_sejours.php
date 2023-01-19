<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Dmp\CDMPDocument;
use Ox\Mediboard\Atih\CGroupage;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CReadFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$date_min = CView::get("date_min", "date default|" . CMbDT::date("-1 DAY"), true);
$date_max = CView::get("date_max", "date default|now", true);
$page     = CView::get("page", "num default|0");
$types    = CView::get("types", "str", true);
CView::checkin();

$group = CGroups::loadCurrent();

$sejour            = new CSejour();
$where             = [];
$where["entree"] = $sejour->getDS()->prepareBetween("$date_min 00:00:00", "$date_max 23:59:59");
$where["group_id"] = "= '$group->_id'";
$where["annule"]   = "= '0'";
if ($types && !in_array("", $types)) {
    $where["type"] = CSQLDataSource::prepareIn($types);
}
$order   = [];
$order[] = "sortie";
$order[] = "entree";
$step    = 30;
$limit   = "$page,$step";

/** @var CSejour[] $listSejours */
$count       = $sejour->countList($where);
$listSejours = $sejour->loadList($where, $order, $limit);

$patients   = CStoredObject::massLoadFwdRef($listSejours, "patient_id");
$ipps       = CPatient::massLoadIPP($patients);
$ndas       = CSejour::massLoadNDA($listSejours);
$praticiens = CStoredObject::massLoadFwdRef($listSejours, "praticien_id");
CMediusers::massLoadFwdRef($praticiens, "function_id");
CStoredObject::massLoadFwdRef($listSejours, "group_id");
CStoredObject::massLoadFwdRef($listSejours, "etablissement_sortie_id");
CStoredObject::massLoadFwdRef($listSejours, "service_sortie_id");
CStoredObject::massLoadFwdRef($listSejours, "service_sortie_id");

CSejour::massCountActes($listSejours);

$dmp_active = CModule::getActive("dmp");
$groupages  = [];

foreach ($listSejours as $_sejour) {
    $_sejour->loadRefsDocItems();
    $_sejour->_ref_patient = $patients[$_sejour->patient_id];
    $_sejour->loadRefPraticien();
    $_sejour->loadExtCodesCCAM();
    $_sejour->loadRefFacture();
    $_sejour->countActes();
    $_sejour->countActes();
    $traitement_dossier = $_sejour->loadRefTraitementDossier();
    $traitement_dossier->loadRefDim();

    if (CModule::getActive("atih")) {
        $groupage = new CGroupage();

        if (!in_array($_sejour->type, CSejour::getTypesSejoursUrgence($_sejour->praticien_id))) {
            $rss = $_sejour->loadRefRSS();

            if ($_sejour->_ref_traitement_dossier && $_sejour->_ref_traitement_dossier->_id && $rss->_id) {
                $groupage->launchFG($rss->_id);
            }
        }

        $groupages[$_sejour->_id] = $groupage;
    }

    if ($dmp_active) {
        $_sejour->_ref_patient->loadLastId400("DMP $_sejour->group_id");

        if ($_sejour->_ref_patient->_ref_last_id400->id400 == 4) {
            $_sejour->_count_dmp_docs = CDMPDocument::countDMPDocumentSejour($_sejour);
        }
    }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("listSejours", $listSejours);
$smarty->assign('notReadFiles', CReadFile::getUnread($listSejours));
$smarty->assign("page", $page);
$smarty->assign("count", $count);
$smarty->assign("step", $step);
$smarty->assign("groupages", $groupages);
$smarty->display("current_dossiers/inc_current_sejours");
