<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Urgences\CExtractPassages;

CCanDo::checkRead();

$extractPassages = new CExtractPassages();

// Création du template
// Mettre car inclusion dans les modules externes
$smarty = new CSmartyDP("modules/dPurgences");

$smarty->assign("extractPassages", $extractPassages);
$smarty->assign("types", $types);

$smarty->display("extract_manuel");
