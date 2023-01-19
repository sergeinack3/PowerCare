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

/**
 * View Edit Source
 */
CCanDo::checkEdit();

$source_id = CView::get("source_id", "num default|0", true);
CView::setSession("class", "CSourceLPR");

CView::checkin();

$source_lpr = new CSourceLPR();
$source_lpr->load($source_id);

if (!$source_lpr->_id) {
  $source_lpr->valueDefaults();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("source_lpr", $source_lpr);

$smarty->display("inc_edit_source_lpr.tpl");
