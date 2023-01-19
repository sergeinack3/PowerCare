<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp\Controllers\Legacy;

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Ftp\CFTP;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Core\CLegacyController;

class SFTPLegacyController extends CLegacyController
{
    public function ajaxConnexionSFTP(): void
    {
        CCanDo::check();

        // Check params
        $exchange_source_name = CView::get("exchange_source_name", "str");
        CView::checkin();

        if ($exchange_source_name == null) {
            CAppUI::stepMessage(UI_MSG_ERROR, "CSourceFTP-no-source", $exchange_source_name);
        }

        /** @var CSourceSFTP $exchange_source */
        $exchange_source = CExchangeSource::get($exchange_source_name, CSourceSFTP::TYPE, false, null, false);
        if (!$exchange_source->_id) {
            CAppUI::stepMessage(UI_MSG_ERROR, "CExchangeSource-no-source", $exchange_source_name);
        }

        $connection = false;
        try {
            $client = $exchange_source->getClient();
            if ($connection = $client->isReachableSource()) {
                CAppUI::stepMessage(
                    E_USER_NOTICE,
                    "CSFTP-success-connection",
                    $exchange_source->host,
                    $exchange_source->port
                );

                if ($connection = $client->isAuthentificate()) {
                    CAppUI::stepMessage(E_USER_NOTICE, "CSFTP-success-authentification", $exchange_source->user);

                    $sent_file = CAppUI::conf('root_dir') . "/ping.php";
                    $remote_file = $exchange_source->fileprefix . "test.txt";

                    // prefix already given and we don't want use prefix like a directory
                    $exchange_source->fileprefix = null;

                    $client->addFile($remote_file, $sent_file);
                    CAppUI::stepMessage(E_USER_NOTICE, "CSFTP-success-transfer_out", $sent_file, $remote_file);

                    $get_file = "tmp/ping.php";
                    $client->getData($remote_file);
                    CAppUI::stepMessage(E_USER_NOTICE, "CSFTP-success-transfer_in", $remote_file, $get_file);

                    $client->delFile($remote_file);
                    CAppUI::stepMessage(E_USER_NOTICE, "CFTP-success-deletion", $remote_file);
                }
            }
        } catch (CMbException $e) {
            $e->stepAjax();
            if (count($client->connexion->getSFTPErrors())) {
                CAppUI::stepMessage(UI_MSG_ERROR, $client->connexion->getLastSFTPError());
            }
        }

        if (!$connection && $exchange_source->_message) {
            CAppUI::stepMessage(UI_MSG_ERROR, $exchange_source->_message);
        } elseif (!$connection) {
            CAppUI::stepMessage(
                UI_MSG_ERROR,
                'CSourceSFTP-connexion-failed',
                "{$exchange_source->host}:{$exchange_source->port}"
            );
        }
    }

    public function ajaxGetFilesSFTP(): void
    {
        CCanDo::check();

        // Check params
        $exchange_source_name = CView::get("exchange_source_name", "str");
        CView::checkin();

        if ($exchange_source_name == null) {
            CAppUI::stepMessage(UI_MSG_ERROR, "CSourceFTP-no-source", $exchange_source_name);
        }

        /** @var CSourceSFTP $exchange_source */
        $exchange_source = CExchangeSource::get($exchange_source_name, CSourceSFTP::TYPE, true, null, false);
        if (!$exchange_source->_id) {
            CAppUI::stepMessage(UI_MSG_ERROR, "CExchangeSource-no-source", $exchange_source_name);
        }

        $connection = false;
        try {
            if ($connection = $exchange_source->getClient()->isAuthentificate()) {
                CAppUI::stepMessage(
                    UI_MSG_OK,
                    "CSFTP-success-connection",
                    $exchange_source->host,
                    $exchange_source->user
                );

                $files = $exchange_source->getClient()->getListFilesDetails($exchange_source->fileprefix, true);
            }
        } catch (CMbException $e) {
            $e->stepMessage();
            CAppUI::stepMessage(UI_MSG_ERROR, $exchange_source->getClient()->getError());
        }

        if ($connection) {
            // Création du template
            $smarty = new CSmartyDP();
            $smarty->assign("exchange_source", $exchange_source);
            $smarty->assign("files", $files);
            $smarty->display("inc_ftp_files.tpl");
        } elseif ($exchange_source->_message) {
            CAppUI::stepMessage(UI_MSG_ERROR, $exchange_source->_message);
        } else {
            CAppUI::stepMessage(
                UI_MSG_ERROR,
                'CSourceSFTP-connexion-failed',
                "{$exchange_source->host}:{$exchange_source->port}"
            );
        }
    }
}
