<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

/**
 * Modification des param�tres d'alt�ration de cha�nes de caract�res
 */
CCanDo::checkAdmin();

$action_type  = CView::get("action_type","str notNull");

CView::checkin();

// Cr�ation du template
$smarty = new CSmartyDP();
$smarty->assign("action_type", $action_type);

$smarty->display("inc_edit_params_rule.tpl");