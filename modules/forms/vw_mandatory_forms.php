<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();

$date       = CView::get('date', 'date');
$service_id = CView::get('service_id', 'str');

CView::checkin();

CView::enableSlave();

$group = CGroups::loadCurrent();
$date  = ($date) ?: CMbDT::date();

$where = array(
  'externe'   => "= '0'",
  'cancelled' => "= '0'",
);

$service  = new CService();
$services = $service->loadListWithPerms(PERM_READ, $where);

// Récuperation du service par défaut dans les préférences utilisateur
$default_services_id = CAppUI::pref('default_services_id', '{}');

// Récuperation du service à afficher par défaut (on prend le premier s'il y en a plusieurs)
$default_service_id  = '';
$default_services_id = json_decode($default_services_id);

if (isset($default_services_id->{"g{$group->_id}"})) {
  $default_service_id = explode('|', $default_services_id->{"g{$group->_id}"});
  $default_service_id = reset($default_service_id);
}

if (!$service_id && $default_service_id && !CAppUI::conf('soins Sejour select_services_ids', $group)) {
  $service_id = $default_service_id;
}

$smarty = new CSmartyDP();
$smarty->assign('date', $date);
$smarty->assign('services', $services);
$smarty->assign('default_service_id', $default_service_id);
$smarty->assign('service_id', $service_id);
$smarty->display('vw_mandatory_forms.tpl');