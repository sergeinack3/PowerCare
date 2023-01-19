<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\Timeline\Category\AppointmentAnesthCategoryMaternite;
use Ox\Mediboard\Maternite\Timeline\Category\AppointmentCategoryMaternite;
use Ox\Mediboard\Maternite\Timeline\Category\BirthCategoryMaternite;
use Ox\Mediboard\Maternite\Timeline\Category\DocumentCategoryMaternite;
use Ox\Mediboard\Maternite\Timeline\Category\ExpectedTermCategoryMaternite;
use Ox\Mediboard\Maternite\Timeline\Category\FileCategoryMaternite;
use Ox\Mediboard\Maternite\Timeline\Category\FormCategoryMaternite;
use Ox\Mediboard\Maternite\Timeline\Category\PregnancyCategoryMaternite;
use Ox\Mediboard\Maternite\Timeline\Category\StayCategoryMaternite;
use Ox\Mediboard\Maternite\Timeline\Category\SurgeryCategoryMaternite;
use Ox\Mediboard\Maternite\Timeline\TimelineFactoryMaternite;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Timeline\MenuTimelineCategory;
use Ox\Mediboard\System\Timeline\Timeline;
use Ox\Mediboard\System\Timeline\TimelineFactory;
use Ox\Mediboard\System\Timeline\TimelineMenu;

CCanDo::checkRead();

$pregnancy_id        = CView::get('pregnancy_id', 'ref class|CGrossesse');
$refresh             = CView::get("refresh", "bool default|0");
$menu_filter         = (array)json_decode(
    stripslashes(CView::get("menu_filter", ['prop' => 'str']))
); // Remove empty values
$filter_practitioner = CView::get("practitioner_filter", "ref class|CMediusers"); // Filter the practitioner

CView::checkin();

if (!$refresh) {
    $smarty = new CSmartyDP();
    $smarty->assign('pregnancy_id', $pregnancy_id);
    $smarty->display('pregnancy_timeline');

    return;
}

$pregnancy = CGrossesse::findOrFail($pregnancy_id);
if (!$pregnancy->_id) {
    throw new Exception('Missing pregnancy id');
}

$patient        = $pregnancy->loadRefParturiente();
$filtered_users = ($filter_practitioner) ? [CMediusers::get($filter_practitioner)] : [];

TimelineFactoryMaternite::$patient   = $patient;
TimelineFactoryMaternite::$pregnancy = $pregnancy;

$appointments_cat = TimelineFactoryMaternite::makeCategory(new AppointmentCategoryMaternite(), $filtered_users);
$documents_cat    = TimelineFactoryMaternite::makeCategory(new DocumentCategoryMaternite(), $filtered_users);
$files_cat        = TimelineFactoryMaternite::makeCategory(new FileCategoryMaternite(), $filtered_users);
$forms_cat        = TimelineFactoryMaternite::makeCategory(new FormCategoryMaternite(), $filtered_users);
$pregnancy_cat    = TimelineFactoryMaternite::makeCategory(new PregnancyCategoryMaternite(), $filtered_users);
$birth_cat        = TimelineFactoryMaternite::makeCategory(new BirthCategoryMaternite(), $filtered_users);
$expected_cat     = TimelineFactoryMaternite::makeCategory(new ExpectedTermCategoryMaternite(), $filtered_users);

if (CAppUI::pref("UISTYLE") !== "tamm") {
    $surgeries_cat = TimelineFactoryMaternite::makeCategory(new SurgeryCategoryMaternite(), $filtered_users);
    $stays_cat     = TimelineFactoryMaternite::makeCategory(new StayCategoryMaternite(), $filtered_users);
    $anesth_cat    = TimelineFactoryMaternite::makeCategory(new AppointmentAnesthCategoryMaternite(), $filtered_users);
}

$menu_factory = new TimelineFactory();
$menu_factory->setFilteredMenus($menu_filter);

$appointments_menu = $menu_factory->makeMenu(
    'medical',
    'fas fa-user-md',
    'CConsultation|pl',
    MenuTimelineCategory::APPOINTMENTS()
)
    ->withChild('appointments', 'fas fa-user-md', 'CConsultation|pl', $appointments_cat)
    ->getMenu();

$pregnancy_menu = $menu_factory->makeMenu(
    'pregnancy',
    'fas fa-female',
    'CGrossesse',
    MenuTimelineCategory::PREGNANCY(),
    $pregnancy_cat
)
    ->withVisibility(false)
    ->getMenu();

$birth_menu = $menu_factory->makeMenu('birth', 'fa fa-child', 'CNaissance', MenuTimelineCategory::BIRTH(), $birth_cat)
    ->withVisibility(false)
    ->getMenu();

$expected_menu = $menu_factory->makeMenu(
    'expected_term',
    'fa fa-clock',
    'CGrossesse-terme_prevu',
    MenuTimelineCategory::PREGNANCY(),
    $expected_cat
)
    ->withVisibility(false)
    ->getMenu();

$documents_menu = $menu_factory->makeMenu(
    'folders',
    'fas fa-file-alt',
    'CCompteRendu|pl',
    MenuTimelineCategory::DOCUMENTS()
)
    ->withChild('documents', 'fas fa-file-alt', 'CCompteRendu|pl', $documents_cat)
    ->withChild('files', 'fas fa-file-alt', 'CFile|pl', $files_cat)
    ->withChild('forms', 'fas fa-list', 'CExClass|pl', $forms_cat)
    ->getMenu();

$stays_menu     = null;
$surgeries_menu = null;

if (CAppUI::pref("UISTYLE") !== "tamm") {
    $appointments_menu = $menu_factory->makeMenu(
        'medical',
        'fas fa-user-md',
        'CConsultation|pl',
        MenuTimelineCategory::APPOINTMENTS()
    )
        ->withChild('appointments', 'fas fa-user-md', 'CConsultation|pl', $appointments_cat)
        ->withChild('anesth_appointments', 'fas fa-user-md', 'CConsultAnesth|pl', $anesth_cat)
        ->getMenu();

    $stays_menu = $menu_factory->makeMenu('stays', 'fas fa-bed', 'CSejour|pl', MenuTimelineCategory::STAY(), $stays_cat)
        ->getMenu();

    $surgeries_menu = $menu_factory->makeMenu(
        'surgeries',
        'icon-i-surgery',
        'COperation|pl',
        MenuTimelineCategory::SURGERY(),
        $surgeries_cat
    )
        ->getMenu();
}

$menu = new TimelineMenu(
    $appointments_menu,
    $pregnancy_menu,
    $birth_menu,
    $expected_menu,
    $documents_menu,
    $stays_menu,
    $surgeries_menu
);
$menu->setSelectedMenus($menu_filter);

$timeline = new Timeline($menu->getMenuInstances());
$timeline->buildTimeline();

$actions_files = array_diff(scandir(__DIR__ . '/templates/timeline/actions'), ['..', '.']);

$smarty = new CSmartyDP();

$smarty->assign('base', $pregnancy);
$smarty->assign('user', CMediusers::get());

$smarty->assign('timeline', $timeline);
$smarty->assign('filtered_menus', $menu_filter);
$smarty->assign('menu_classes', $menu->getClasses());
$smarty->assign('actions_files', $actions_files);
$smarty->assign('filtered_practitioners', $filter_practitioner);
$smarty->assign('today', CMbDT::date());
$smarty->assign('selected_menus', $menu_filter);

$smarty->display('timeline/inc_pregnancy_timeline');
