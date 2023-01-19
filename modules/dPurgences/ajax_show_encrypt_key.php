<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Interop\Ror\CRORException;
use Ox\Interop\Ror\CRORFactory;

CCanDo::checkAdmin();

// Appel de la fonction d'extraction du RPUSender
try {
    $rpu_sender = CRORFactory::getSender();
    CApp::log('Urgences show encrypt key', $rpu_sender->showEncryptKey());
} catch (CRORException $exception) {
    CAppUI::stepAjax($exception->getMessage(), UI_MSG_ERROR);
}
