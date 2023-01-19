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

/**
 * View Printing Sources
 */
CCanDo::checkEdit();

$source_id = CView::get("source_id", "num default|0", true);
$class     = CView::get("class", "enum list|CSourceLPR|CSourceSMB default|CSourceLPR", true);

CView::checkin();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("source_id", $source_id);
$smarty->assign("class", $class);

$smarty->display("vw_idx_sources.tpl");
