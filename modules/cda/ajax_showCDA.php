<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CView;
use Ox\Interop\Cda\CCdaTools;

$message = CView::post("message", "str");
CView::checkin();
$message = stripslashes($message);

echo CCdaTools::showXSLT($message);