<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;

CCanDo::check();

$user = CMediusers::get();

if (!$user || !$user->_id) {
  CAppUI::commonError();
}

$user->loadRefsNotes();

$smarty = new CSmartyDP();
$smarty->assign('user', $user);
$smarty->assign('spec_cpam', CSpecCPAM::getList());
$smarty->display('edit_professional_context');