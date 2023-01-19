<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\Timeline\Category\AllergyCategoryConsultation;
use Ox\Mediboard\Cabinet\Timeline\Category\AnesthAppointmentCategoryConsultation;
use Ox\Mediboard\Cabinet\Timeline\Category\AntecedentCategoryConsultation;
use Ox\Mediboard\Cabinet\Timeline\Category\AppointmentCategoryConsultation;
use Ox\Mediboard\Cabinet\Timeline\Category\BirthCategoryConsultation;
use Ox\Mediboard\Cabinet\Timeline\Category\LabCategoryConsultation;
use Ox\Mediboard\Cabinet\Timeline\Category\StayCategoryConsultation;
use Ox\Mediboard\Cabinet\Timeline\Category\SurgeryCategoryConsultation;
use Ox\Mediboard\Cabinet\Timeline\Category\VitalCategoryConsultation;
use Ox\Mediboard\Cabinet\Timeline\TimelineFactoryConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Timeline\MenuTimelineCategory;
use Ox\Mediboard\System\Timeline\Timeline;
use Ox\Mediboard\System\Timeline\TimelineFactory;
use Ox\Mediboard\System\Timeline\TimelineMenu;

CCanDo::checkRead();

$appointment_id      = CView::getRefCheckRead('appointment_id', 'ref class|CConsultation', true);
$refresh             = CView::get('refresh', 'bool default|0');
$filtered_menus      = (array)json_decode(stripslashes(CView::get('menus_filter', ['prop' => 'str'])), true); // Remove empty values
$filter_practitioner = CView::get('filter_user_id', 'ref class|CMediusers'); // Filter the practitioner

CView::checkin();

$appointment = CConsultation::findOrFail($appointment_id);

$filter_practitioner = ($filter_practitioner) ? [CMediusers::findOrFail($filter_practitioner)] : [];

$active_lab_modules = [];
if (CModule::getActive('mondialSante')) {
    $active_lab_modules[] = 'mondialSante';
}
if (CModule::getActive('mssante')) {
    $active_lab_modules[] = 'mssante';
}

TimelineFactoryConsultation::$patient = $appointment->loadRefPatient();

$appointment_cat = TimelineFactoryConsultation::makeCategory(new AppointmentCategoryConsultation(), $filter_practitioner);
$anesth_cat      = TimelineFactoryConsultation::makeCategory(new AnesthAppointmentCategoryConsultation(), $filter_practitioner);
$vital_cat       = TimelineFactoryConsultation::makeCategory(new VitalCategoryConsultation(), $filter_practitioner);
$allergy_cat     = TimelineFactoryConsultation::makeCategory(new AllergyCategoryConsultation(), $filter_practitioner);
$antecedent_cat  = TimelineFactoryConsultation::makeCategory(new AntecedentCategoryConsultation(), $filter_practitioner);
$birth_cat       = TimelineFactoryConsultation::makeCategory(new BirthCategoryConsultation(), $filter_practitioner);
$surgery_cat     = TimelineFactoryConsultation::makeCategory(new SurgeryCategoryConsultation(), $filter_practitioner);
$stay_cat        = TimelineFactoryConsultation::makeCategory(new StayCategoryConsultation(), $filter_practitioner);

if (count($active_lab_modules) > 0) {
    $lab_category = TimelineFactoryConsultation::makeCategory(new LabCategoryConsultation(), $filter_practitioner);

    if ($lab_category instanceof LabCategoryConsultation) {
        $lab_category->setActiveModules($active_lab_modules);
        $lab_category->setSelectedPractitioner($appointment->loadRefPraticien());
    }
}

$menu_factory = new TimelineFactory();
$menu_factory->setFilteredMenus($filtered_menus);

$birth_menu = $menu_factory->makeMenu('birth', 'fas fa-child', 'CUser-user_birthday', MenuTimelineCategory::BIRTH(), $birth_cat)
    ->withVisibility(false)
    ->getMenu();

// Consultation menu
$appointments_menu = $menu_factory->makeMenu(
    'appointments',
    'fas fa-user-md',
    'CConsultation|pl',
    MenuTimelineCategory::APPOINTMENTS(),
    $appointment_cat
)
    ->withChild('anesth_appointments', 'fas fa-user-md', 'CConsultAnesth|pl', $anesth_cat)
    ->getMenu();

// Constants menu
$vital_menu = $menu_factory->makeMenu('vitals', 'fas fa-chart-bar', 'CAbstractConstant|pl', MenuTimelineCategory::MEDICAL(), $vital_cat)
    ->getMenu();

// Surgeries
$surgeries_menu = $menu_factory->makeMenu(
    'surgeries',
    'icon-i-surgery',
    'COperation|pl',
    MenuTimelineCategory::SURGERY(),
    $surgery_cat
)
    ->getMenu();

// Stays
$stays_menu = $menu_factory->makeMenu('stays', 'fas fa-bed', 'CSejour|pl', MenuTimelineCategory::STAY(), $stay_cat)
    ->withAttachedChild('arrived', 'fas fa-calendar-check', 'Admission')
    ->withAttachedChild('left', 'fas fa-calendar-times', 'Sortie')
    ->getMenu();

// Antecedent menu
$atcds_menu = $menu_factory->makeMenu('antecedents', 'fas fa-asterisk', 'CAntecedent|pl', MenuTimelineCategory::MEDICAL())
    ->withChild('allergies', 'fas fa-exclamation-circle', 'CAntecedent-Allergie', $allergy_cat)
    ->withChild('antecedent', 'fas fa-asterisk', 'CAntecedent|pl', $antecedent_cat)
    ->getMenu();

$lab_menu = null;
if (count($active_lab_modules) > 0) {
    $lab_menu = $menu_factory->makeMenu('lab', 'fas fa-microscope', 'CExamComp-labo', MenuTimelineCategory::OTHER(), $lab_category)
        ->getMenu();
}

$menu = new TimelineMenu($birth_menu, $appointments_menu, $atcds_menu, $vital_menu, $stays_menu, $surgeries_menu, $lab_menu);
$menu->setSelectedMenus($filtered_menus);

$timeline = new Timeline($menu->getMenuInstances());
$timeline->buildTimeline();

$actions_files = array_diff(scandir(__DIR__ . '/templates/timeline/actions'), ['..', '.']);

$smarty = new CSmartyDP();

$smarty->assign('base', $appointment);
$smarty->assign('user', CMediusers::get());

$smarty->assign('timeline', $timeline);
$smarty->assign('filtered_menus', $filtered_menus);
$smarty->assign('selected_menus', $menu->getSelectedMenus());
$smarty->assign('menu_classes', $menu->getClasses());
$smarty->assign('filtered_practitioners', $filter_practitioner);
$smarty->assign('today', CMbDT::date());
$smarty->assign('actions_files', $actions_files);

if ($refresh) {
    $smarty->display('timeline/inc_timeline_appointment');

    return;
}

$smarty->display('appointment_timeline');
