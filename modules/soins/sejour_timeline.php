<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Soins\Timeline\Category\AnesthAppointmentCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\AnesthVisitCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\AppointmentCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\ArrivedCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\AssignmentBeginCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\AssignmentEndCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\DocumentCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\FileCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\FormCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\LeftCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\MovementCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\ObservationCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\PrescriptionBeginCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\PrescriptionCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\PrescriptionEndCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\ScoreCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\SurgeryCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\TransmissionCategorySoins;
use Ox\Mediboard\Soins\Timeline\Category\VitalCategorySoins;
use Ox\Mediboard\Soins\Timeline\TimelineFactorySoins;
use Ox\Mediboard\System\Timeline\MenuTimelineCategory;
use Ox\Mediboard\System\Timeline\Timeline;
use Ox\Mediboard\System\Timeline\TimelineFactory;
use Ox\Mediboard\System\Timeline\TimelineMenu;

CCanDo::checkRead();

$stay_id             = CView::get("sejour_id", "ref class|CSejour", true);
$refresh             = CView::get("refresh", "bool default|0");
$filtered_menu       = (array)json_decode(stripslashes(CView::get("menu_filter", ['prop' => 'str']))); // Remove empty values
$filter_practitioner = CView::get("practitioner_filter", "ref class|CMediusers"); // Filter the practitioner

CView::checkin();

$filter_practitioner = ($filter_practitioner) ? [CMediusers::findOrFail($filter_practitioner)] : [];

// If it's the first display, just display and stop the script
if (!$refresh) {
  (new CSmartyDP())->display('sejour_timeline');

  return;
}

$stay             = CSejour::findOrFail($stay_id);
$patient          = $stay->loadRefPatient();
$addictology_file = $stay->loadRefDossierAddictologie();

TimelineFactorySoins::$patient = $patient;
TimelineFactorySoins::$stay    = $stay;

$m1_cat        = TimelineFactorySoins::makeCategory(new AppointmentCategorySoins(), $filter_practitioner);
$m2_cat        = TimelineFactorySoins::makeCategory(new AnesthAppointmentCategorySoins(), $filter_practitioner);
$m3_cat        = TimelineFactorySoins::makeCategory(new AnesthVisitCategorySoins(), $filter_practitioner);
$d1_cat        = TimelineFactorySoins::makeCategory(new DocumentCategorySoins(), $filter_practitioner);
$d2_cat        = TimelineFactorySoins::makeCategory(new FileCategorySoins(), $filter_practitioner);
$d3_cat        = TimelineFactorySoins::makeCategory(new FormCategorySoins(), $filter_practitioner);
$p1_cat        = TimelineFactorySoins::makeCategory(new PrescriptionCategorySoins(), $filter_practitioner);
$p2_cat        = TimelineFactorySoins::makeCategory(new PrescriptionBeginCategorySoins(), $filter_practitioner);
$p3_cat        = TimelineFactorySoins::makeCategory(new PrescriptionEndCategorySoins(), $filter_practitioner);
$mo1_cat       = TimelineFactorySoins::makeCategory(new MovementCategorySoins(), $filter_practitioner);
$mo2_cat       = TimelineFactorySoins::makeCategory(new AssignmentBeginCategorySoins(), $filter_practitioner);
$mo3_cat       = TimelineFactorySoins::makeCategory(new AssignmentEndCategorySoins(), $filter_practitioner);
$mo4_cat       = TimelineFactorySoins::makeCategory(new ArrivedCategorySoins(), $filter_practitioner);
$mo5_cat       = TimelineFactorySoins::makeCategory(new LeftCategorySoins(), $filter_practitioner);
$v1_cat        = TimelineFactorySoins::makeCategory(new VitalCategorySoins(), $filter_practitioner);
$v2_cat        = TimelineFactorySoins::makeCategory(new TransmissionCategorySoins(), $filter_practitioner);
$v3_cat        = TimelineFactorySoins::makeCategory(new ObservationCategorySoins(), $filter_practitioner);
$v4_cat        = TimelineFactorySoins::makeCategory(new ScoreCategorySoins(), $filter_practitioner);
$surgeries_cat = TimelineFactorySoins::makeCategory(new SurgeryCategorySoins(), $filter_practitioner);

$menu_factory = new TimelineFactory();
$menu_factory->setFilteredMenus($filtered_menu);

// Menus
$medical_menu = $menu_factory->makeMenu('medical', 'fas fa-user-md', 'Medical', MenuTimelineCategory::APPOINTMENTS())
  ->withChild('appointments', 'fas fa-user-md', 'CConsultation', $m1_cat)
  ->withChild('anesth_appointments', 'fas fa-user-md', 'CConsultAnesth', $m2_cat)
  ->withChild('anesth_visits', 'fas fa-user-md', 'Visites anesth', $m3_cat)
  ->getMenu();

$movements_menu = $menu_factory->makeMenu('movements', 'fas fa-arrows-alt', 'Mouvements', MenuTimelineCategory::MEDICAL())
  ->withChild('brancardage', 'fas fa-bed', 'CBrancardage', $mo1_cat)
  ->withChild('assignment_begin', 'fas fa-calendar-check', 'CAffectation-Begin', $mo2_cat)
  ->withChild('assignment_end', 'fas fa-calendar-times', 'CAffectation-End', $mo3_cat)
  ->withChild('arrived', 'fas fa-calendar-check', 'Admission', $mo4_cat)
  ->withChild('left', 'fas fa-calendar-times', 'CAffectation-sortie', $mo5_cat)
  ->getMenu();

$documents_menu = $menu_factory->makeMenu('folders', 'fas fa-file-alt', 'CCompteRendu|pl', MenuTimelineCategory::DOCUMENTS())
  ->withChild('documents', 'fas fa-file-alt', 'CCompteRendu|pl', $d1_cat)
  ->withChild('files', 'fas fa-file-alt', 'CFile|pl', $d2_cat)
  ->withChild('forms', 'fas fa-list', 'CExClass|pl', $d3_cat)
  ->getMenu();

$prescriptions_menu = $menu_factory->makeMenu('prescriptions', 'fas fa-file', 'CPrescription|pl', MenuTimelineCategory::DOCUMENTS())
  ->withChild('administer', 'fas fa-prescription-bottle-alt', 'CPrescriptionLineElement-event-administration', $p1_cat)
  ->withChild('prescription_begin', 'fas fa-prescription-bottle-alt', 'Début de prescription', $p2_cat)
  ->withChild('prescription_end', 'fas fa-prescription-bottle-alt', 'Fin de prescription', $p3_cat)
  ->getMenu();

$vitals_menu = $menu_factory->makeMenu('nurse', 'fas fa-chart-bar', 'CAbstractConstant|pl', MenuTimelineCategory::MEDICAL())
  ->withChild('vitals', 'fas fa-chart-bar', 'CAbstractConstant|pl', $v1_cat)
  ->withChild('transmissions', 'fas fa-comment-dots', 'CTransmissionMedicale|pl', $v2_cat)
  ->withChild('observations', 'fas fa-notes-medical', 'CObservationMedicale|pl', $v3_cat)
  ->withChild('score', 'fas fa-calculator', 'CPrescription-score', $v4_cat)
  ->getMenu();

$surgeries_menu = $menu_factory->makeMenu('surgeries', 'icon-i-surgery', 'COperation|pl', MenuTimelineCategory::SURGERY())
  ->getMenu();

$menu = new TimelineMenu($medical_menu, $movements_menu, $documents_menu, $prescriptions_menu, $vitals_menu);
$menu->setSelectedMenus($filtered_menu);

$timeline = new Timeline($menu->getMenuInstances());
$timeline->buildTimeline();

$actions_files = array_diff(scandir(__DIR__ . '/templates/timeline/actions'), ['..', '.']);

$smarty = new CSmartyDP();

$smarty->assign('base', $stay);
$smarty->assign('user', CMediusers::get());

$smarty->assign('timeline', $timeline);
$smarty->assign('filtered_menus', $filtered_menu);
$smarty->assign('menu_classes', $menu->getClasses());
$smarty->assign('actions_files', $actions_files);
$smarty->assign('filtered_practitioners', $filter_practitioner);
$smarty->assign('today', CMbDT::date());

$smarty->display('inc_sejour_timeline');
