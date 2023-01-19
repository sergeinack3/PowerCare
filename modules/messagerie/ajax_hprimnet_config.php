<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CSMimeHandler;

CCanDo::checkAdmin();

CView::checkin();

$key_status = file_exists(CSMimeHandler::getMasterKeyPath());
$key_directory_status = file_exists(CAppUI::conf('messagerie hprimnet_key_directory'));
$certifcates_directory_status = file_exists(CAppUI::conf('messagerie hprimnet_certificates_directory'));

$smarty = new CSmartyDP();
$smarty->assign('key_status', $key_status);
$smarty->assign('key_directory_status', $key_directory_status);
$smarty->assign('certifcates_directory_status', $certifcates_directory_status);
$smarty->display('inc_hprimnet_config.tpl');