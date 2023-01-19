<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductEndowment;

CCanDo::checkRead();

$endowment_id = CValue::get('endowment_id');

$endowment = new CProductEndowment();
$endowment->load($endowment_id);
$endowment->loadRefsFwd();
$endowment->updateFormFields();
$endowment->loadRefsBack();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign('endowment', $endowment);
$smarty->display('print_endowment.tpl');

