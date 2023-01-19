<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkRead();

$rpu_id = CView::get("rpu_id", "ref class|CRPU");

CView::checkin();

$rpu = new CRPU();
$rpu->load($rpu_id);
$rpu->loadRefSejour();

// Si accès au module PMSI : peut modifier le diagnostic principal
$access_pmsi = CModule::getCanDo("dPpmsi")->edit;

// Si praticien : peut modifier le CCMU, GEMSA et diagnostic principal
$is_praticien = CMediusers::get()->isPraticien();

$smarty = new CSmartyDP();

$smarty->assign("rpu", $rpu);
$smarty->assign("is_praticien", $is_praticien);
$smarty->assign("access_pmsi", $access_pmsi);

$smarty->display("inc_edit_fields_rpu");
