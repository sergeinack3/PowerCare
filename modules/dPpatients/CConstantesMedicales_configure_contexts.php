<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CSecteur;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkAdmin();

$schema     = CValue::get('schema');
$group_guid = CValue::get('group');

$contexts      = array();
$context_class = '';

$smarty = new CSmartyDP();

$count = 0;
if ($group_guid != 'global') {
  /** @var CGroups $group */
  $group = CGroups::loadFromGuid($group_guid);

  $schema = preg_replace('@([\w ]+ / )@', "", $schema);
  list($context_class, $parent_class) = explode(' ', $schema);

  /** @var CMbObject $context */
  $context           = new $context_class;
  $context->group_id = $group->_id;

  $contexts = $context->loadMatchingList();
  $count    = count($contexts);

  /* Special treatment for the CServices : we need to get the CServices by sectors */
  if ($context_class == 'CService') {
    $service           = new CService();
    $service->group_id = $group->_id;
    $services          = $service->loadMatchingList();

    $sector           = new CSecteur();
    $sector->group_id = $group->_id;
    $sectors          = $sector->loadMatchingList('nom');
    $count            = count($sectors);

    if (!empty($sectors)) {
      foreach ($sectors as $_sector) {
        $_sector->loadRefsServices();
        $services = array_diff($services, $_sector->_ref_services);
      }

      $contexts = $sectors;
      $count    += 1;// Nombre de secteurs + hors secteur
      $smarty->assign('sectors', true);
    }
    else {
      $contexts = $services;
      $count    = count($services);
      $services = array();
    }

    $smarty->assign('out_sector', $services);
  }
}

$smarty->assign('context_class', $context_class);
$smarty->assign('contexts', $contexts);
$smarty->assign('count', $count);
$smarty->display('constantes_configs/inc_contexts.tpl');