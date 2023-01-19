<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Cda\CCdaTools;

$message = CView::post("message", "str");
CView::checkin();

$message = utf8_decode(stripslashes($message));

$treecda = CCdaTools::parse($message);
$xml     = CCdaTools::showxml($message);

$smarty = new CSmartyDP();
$smarty->assign("message", $message);
$smarty->assign("treecda", $treecda);
$smarty->assign("xml"    , $xml);
$smarty->display("inc_highlightcda.tpl");