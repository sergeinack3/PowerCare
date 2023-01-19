<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\FTPClientInterface;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Ftp\CFTP;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\ResilienceFTPClient;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Core\CLegacyController;

class FTPLegacyController extends CLegacyController
{
    public function ajaxConnexionFTP(): void
    {
        CCanDo::check();

        // Check params
        $exchange_source_name = CView::get("exchange_source_name", "str");

        CView::checkin();

        if ($exchange_source_name == null) {
            CAppUI::stepAjax("Aucun nom de source d'échange spécifié", UI_MSG_ERROR);
        }

        $exchange_source = CExchangeSource::get($exchange_source_name, CSourceFTP::TYPE, false, null, false);

        /** @var FTPClientInterface $ftp */
        $ftp = $exchange_source->getClient();
        $ftp->init($exchange_source);
        if ($ftp instanceof ResilienceFTPClient) {
            $source = $ftp->client->_source;
        } else {
            $source = $ftp->_source;
        }

        try {
            if ($ftp->isReachableSource() === true) {
                CAppUI::stepMessage(E_USER_NOTICE, "CFTP-success-connection", $source->host, $source->port);
            }

            if ($ftp->isAuthentificate() === true) {
                CAppUI::stepMessage(E_USER_NOTICE, "CFTP-success-authentification", $source->user);
            }

            if ($source->pasv) {
                CAppUI::stepMessage(E_USER_NOTICE, "CFTP-msg-passive_mode");
            }

            $sent_file   = CAppUI::conf('root_dir') . "/ping.php";
            $remote_file = $source->fileprefix . "test.txt";

            $ftp->addFile($sent_file, $remote_file);
            CAppUI::stepMessage(E_USER_NOTICE, "CFTP-success-transfer_out", $sent_file, $remote_file);

            $get_file = "tmp/ping.php";
            $ftp->getData($remote_file, $get_file);
            CAppUI::stepMessage(E_USER_NOTICE, "CFTP-success-transfer_in", $remote_file, $get_file);

            $source->_destination_file = '.';
            $ftp->delFile($remote_file);
            CAppUI::stepMessage(E_USER_NOTICE, "CFTP-success-deletion", $remote_file);
        } catch (CMbException $e) {
            CAppUI::stepMessage(UI_MSG_ERROR, $e->getMessage());
            CApp::rip();
        }
    }

    public function ajaxGetFilesFTP(): void
    {
        try {
            if (null == $exchange_source_name = CValue::get("exchange_source_name")) {
                CAppUI::stepMessage(UI_MSG_ERROR, "Aucun nom de source d'échange spécifié");
            }

            $exchange_source = CExchangeSource::get($exchange_source_name, CSourceFTP::TYPE, true, null, false);

            $ftp = $exchange_source->getClient();
            $ftp->init($exchange_source);

            if ($ftp instanceof ResilienceFTPClient) {
                $source = $ftp->client->_source;
            } else {
                $source = $ftp->_source;
            }

            $ftp->isReachableSource();
            CAppUI::stepMessage(E_USER_NOTICE, "CFTP-success-connection", $source->host, $source->port);
            $ftp->isAuthentificate();
            CAppUI::stepMessage(E_USER_NOTICE, "CFTP-success-authentification", $source->user);

            if ($source->pasv) {
                CAppUI::stepMessage(E_USER_NOTICE, "CFTP-msg-passive_mode");
            }

            if ($source->fileprefix == null) {
                $source->fileprefix = "";
            }
            $files = $ftp->getListFilesDetails($source->fileprefix);
        } catch (CMbException $e) {
            CAppUI::stepMessage(UI_MSG_ERROR, $e->getMessage());

            return;
        }

        // Création du template
        $smarty = new CSmartyDP();

        $smarty->assign("exchange_source", $exchange_source);
        $smarty->assign("files", $files);

        $smarty->display("inc_ftp_files.tpl");
    }
}
