<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\Files\CUploader;

CCanDo::checkAdmin();

$uploader = new CUploader();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($uploader->checkChunks()) {
        header("HTTP/1.0 200 OK");
    } else {
        header("HTTP/1.0 404 Not Found");
    }
} else {
    $uploader->handleRequest();
}


foreach ($uploader->getMessages() as $_msg) {
    CAppUI::setMsg(...$_msg);
}

echo CAppUI::getMsg();
