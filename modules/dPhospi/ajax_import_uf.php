<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;

CCanDo::checkAdmin();

$uf = new CUniteFonctionnelle();

$import_specs = array(
  "code"        => $uf->_props['code'],
  "libelle"     => $uf->_props['libelle'],
  "type"        => $uf->_props['type'],
  "type_sejour" => $uf->_props['type_sejour']
);

$smarty = new CSmartyDP();
$smarty->assign('import_specs', $import_specs);
$smarty->display('inc_import_uf.tpl');