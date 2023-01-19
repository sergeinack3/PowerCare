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
use Ox\Mediboard\Printing\CSourceSMB;

/**
 * View Edit Source
 */
CCanDo::checkEdit();

$source_id = CView::get("source_id", "num default|0", true);
CView::setSession("class", "CSourceSMB");

CView::checkin();

$source_smb = new CSourceSMB();
$source_smb->load($source_id);

if (!$source_smb->_id) {
  $source_smb->valueDefaults();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("source_smb", $source_smb);

$smarty->display("inc_edit_source_smb.tpl");
