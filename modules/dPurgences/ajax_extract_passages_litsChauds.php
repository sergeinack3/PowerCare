<?php
/**
 * @package Mediboard\dPurgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Interop\Ror\CRORException;
use Ox\Interop\Ror\CRORFactory;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Urgences\CExtractPassages;

CCanDo::checkAdmin();

CApp::setMemoryLimit("512M");

$debut_selection = CView::get("debut_selection", "dateTime");

CView::checkin();

$now = $debut_selection ?: CMbDT::dateTime();

$group = CGroups::loadCurrent();

$extractPassages                  = new CExtractPassages();
$extractPassages->date_extract    = CMbDT::dateTime();
$extractPassages->type            = "litsChauds";
$extractPassages->debut_selection = $now;
$extractPassages->fin_selection   = $now;
$extractPassages->group_id        = $group->_id;
$extractPassages->store();

try {
    $rpuSender       = CRORFactory::getSender();
    $extractPassages = $rpuSender->extractLitsChauds($extractPassages, []);
    if (!$extractPassages || !$extractPassages->_id) {
        throw new CRORException(CRORException::INVALID_DOCUMENT, 'extractPassages');
    }

    echo "<script>RPU_Sender.extract_passages_id = $extractPassages->_id;</script>";
} catch (CRORException $exception) {
    CAppUI::stepAjax($exception->getMessage(), UI_MSG_ERROR);
}
