<?php

/**
 * @package Mediboard\system
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Core\CLegacyController;
use Ox\Mediboard\System\CSourceFileSystem;

class FileSystemLegacyController extends CLegacyController
{
    protected string          $type_action;
    protected string          $exchange_source_name;
    protected CExchangeSource $exchange_source;

    public function initParams(): void
    {
        CCanDo::checkAdmin();

        // Check params
        if (null == $this->exchange_source_name = CValue::get("exchange_source_name")) {
            CAppUI::stepMessage(UI_MSG_ERROR, "Aucun nom de source spécifié");
        }

        if (null == $this->type_action = CValue::get("type_action")) {
            CAppUI::stepMessage(UI_MSG_ERROR, "Aucun type de test spécifié");
        }

        $this->exchange_source = CExchangeSource::get($this->exchange_source_name, "file_system", true, null, false);
    }

    public function ajaxConnexionFileSystem(): void
    {
        $this->initParams();

        // Connexion
        if ($this->type_action == "connexion") {
            try {
                $this->exchange_source->getClient()->isReachableSource();
                $this->exchange_source->getClient()->isAuthentificate();
                CAppUI::stepMessage(UI_MSG_OK, "CSourceFileSystem-host-is-a-dir", $this->exchange_source->host);
            } catch (CMbException $e) {
                CAppUI::stepMessage(UI_MSG_ERROR, $e->getMessage());
            }
        }
    }

    public function ajaxSendFileFileSystem(): void
    {
        $this->initParams();

        if ($this->type_action == "sendFile") {
            try {
                $this->exchange_source->setData("Test source file system in Mediboard", false);
                $this->exchange_source->getClient()->send();
                CAppUI::stepMessage(
                    UI_MSG_OK,
                    "Le fichier 'testSendFile" . $this->exchange_source->fileextension . "' a été copié dans le dossier '" . $this->exchange_source->host . "'"
                );
            } catch (CMbException $e) {
                CAppUI::stepMessage(UI_MSG_ERROR, $e->getMessage());
            }
        }
    }

    public function ajaxGetFileFileSystem(): void
    {
        $this->initParams();

        // Récupération des fichiers
        if ($this->type_action == "getFiles") {
            try {
                $directory = $this->exchange_source->getClient()->getCurrentDirectory();
                $files     = $this->exchange_source->getClient()->getListFilesDetails($directory);

                $count_files = CMbPath::countFiles($this->exchange_source->host);

                CAppUI::stepMessage(
                    UI_MSG_OK,
                    "Le dossier '$this->exchange_source->host' contient : $count_files fichier(s)"
                );
                if ($count_files > 1000) {
                    CAppUI::stepMessage(
                        UI_MSG_WARNING,
                        "Le dossier '" . $this->exchange_source->host . "' contient trop de fichiers pour être listé"
                    );
                }
            } catch (CMbException $e) {
                CAppUI::stepMessage(UI_MSG_ERROR, $e->getMessage());
                CApp::rip();
            }

            // Création du template
            $smarty = new CSmartyDP();
            $smarty->assign("current_directory", $this->exchange_source->getClient()->getCurrentDirectory());
            $smarty->assign("exchange_source", $this->exchange_source);
            $smarty->assign("files", $files);
            $smarty->assign("source_guid", $this->exchange_source->_guid);
            $smarty->display("inc_manage_files.tpl");
        }
    }
}
