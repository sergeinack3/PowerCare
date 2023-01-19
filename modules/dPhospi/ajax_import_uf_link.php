<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkAdmin();

$service = new CService();

$import_specs = array(
  'Nom du Service'      => $service->_props['nom'],
  'Nom de la chambre'   => 'str',
  'Nom du lit'          => 'str',
  'Code UF Hébergement' => 'str',
  'Code UF Soins'       => 'str',
);

$smarty = new CSmartyDP();
$smarty->assign('import_specs', $import_specs);
$smarty->display('inc_import_uf_link.tpl');