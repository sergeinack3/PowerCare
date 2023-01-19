<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CExchangeSource;

CCanDo::checkAdmin();

// Check params
$source_guid = CView::get("source_guid", "str");
CView::checkin();

/** @var CExchangeSource $source */
$source    = CExchangeSource::loadFromGuid($source_guid);
$connexion = false;

try {
    if ($connexion = $source->getClient()->isReachableSource()) {
        $connexion = $source->getClient()->isAuthentificate();
    }

    if (!$connexion && $source->_message) {
        CAppUI::stepMessage(UI_MSG_ERROR, $source->_message);
        CApp::rip();
    } elseif (!$connexion) {
        CAppUI::stepMessage(UI_MSG_ERROR, 'CSourceSFTP-connexion-failed', "{$source->host}:{$source->port}");
        CApp::rip();
    }
} catch (CMbException $e) {
    if ($error = $e->getMessage()) {
        CAppUI::stepMessage(UI_MSG_ERROR, $error);
    }

    return;
}
// Création du template
$smarty = new CSmartyDP();
$smarty->assign("source_guid", $source_guid);
$smarty->display("inc_manage_files.tpl");
