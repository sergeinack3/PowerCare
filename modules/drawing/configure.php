<?php
/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Drawing\CDrawingCategory;

CCanDo::checkAdmin();

$cat  = new CDrawingCategory();
$cats = $cat->loadList(null, "name");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("cats", $cats);
$smarty->display("configure");
