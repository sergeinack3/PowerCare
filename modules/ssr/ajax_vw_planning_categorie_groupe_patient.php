<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CCategorieGroupePatient;
use Ox\Mediboard\Ssr\CPlageGroupePatient;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningWeek;

CCanDo::checkRead();
$categorie_groupe_patient_id = CView::get("categorie_groupe_patient_id", "ref class|CCategorieGroupePatient", true);
$show_inactive               = CView::get("show_inactive", "bool default|0", true);
$date                        = CView::get("day_used", "date default|now", true);
CView::checkin();

$categorie_groupe_patient = new CCategorieGroupePatient();
$categorie_groupe_patient->load($categorie_groupe_patient_id);

$monday = CMbDT::date("monday this week", $date);
$sunday = CMbDT::date("sunday this week", $date);

$planning              = new CPlanningWeek($date, $monday, $sunday, 7, false, "auto", false, true);
$planning->title       = "Groupe de patients - $categorie_groupe_patient->_view";
$planning->guid        = $planning->title;
$planning->hour_min    = "07";
$planning->hour_max    = "19";
$planning->pauses      = ["07", "12", "19"];
$planning->no_dates    = true;
$planning->see_nb_week = true;

$days = [];
for ($i = $monday; $i <= $sunday; $i = CMbDT::date('+1 day', $i)) {
    $days[] = $i;
    $planning->addDayLabel($i, CMbString::capitalize(CMbDT::format($i, "%A %d")));
}

$where["categorie_groupe_patient_id"] = "= '$categorie_groupe_patient_id'";

if (!$show_inactive) {
    $where["actif"] = "= '1'";
}

$plage_groupe_patient = new CPlageGroupePatient();
$plages_groupe        = $plage_groupe_patient->loadList($where);

foreach ($plages_groupe as $_plage) {
    $_plage->_date    = CMbDT::date("$_plage->groupe_day this week", $date);
    $debut            = $_plage->_date . " " . $_plage->heure_debut;
    $categorie        = $_plage->loadRefCategorieGroupePatient();
    $sejours_associes = $_plage->loadRefSejoursAssocies($date);
    $counter_patient  = $sejours_associes && count($sejours_associes) ? count($sejours_associes) : 0;

    $title = $categorie->_view;
    if ($_plage->nom) {
        $title .= " - " . $_plage->nom;
    }

    $title .= "\n $counter_patient " . CAppUI::tr('CPatient-patient');

    //Ajout de l'évènement au planning
    $event              = new CPlanningEvent($_plage->_guid, $debut, $_plage->_duree, $title, null, true);
    $event->type        = "rdvfull";
    $event->plage['id'] = 0;
    $event->important   = $_plage->actif;
    $event->css_class   = $_plage->actif ? "" : "hatching";
    $event->_mode_tooltip = "plage_groupe_view";
    $planning->addEvent($event);
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("planning", $planning);
$smarty->display("inc_vw_week");
