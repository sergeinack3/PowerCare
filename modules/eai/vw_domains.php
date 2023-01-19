<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

/**
 * View interop actors EAI
 */

CCanDo::checkAdmin();

CView::checkin();

// Création du template
$smarty = new CSmartyDP();
$smarty->display("vw_domains.tpl");