<?php
/**
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Printing\CSourceLPR;
use Ox\Mediboard\Printing\CSourceSMB;

/**
 * View Print Sources
 */
CCanDo::checkEdit();

$source_id = CView::get("source_id", "num default|0", true);
$class     = CView::get("class", "enum list|CSourceLPR|CSourceSMB default|CSourceLPR", true);

CView::checkin();

// Récupération des sources
$source_lpr = new CSourceLPR();
$sources    = $source_lpr->loadlist();

$source_smb = new CSourceSMB();
$sources    = array_merge($sources, $source_smb->loadlist());

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sources", $sources);
$smarty->assign("source_id", $source_id);
$smarty->assign("class", $class);

$smarty->display("inc_list_sources.tpl");
