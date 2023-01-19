<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;

CCanDo::checkAdmin();

$file = CValue::files("formfile");

CView::checkin();

if (!$file || !$file['tmp_name']) {
  CAppUI::commonError();
}

if (!$file['tmp_name'][0] || !file_exists($file['tmp_name'][0])) {
  CAppUI::stepAjax('CFile-not-exists', UI_MSG_ERROR, $file['tmp_name'][0]);
}

$all_ids = file_get_contents($file['tmp_name'][0]);
$ids     = explode(';', $all_ids);

$consult  = new CConsultation();
$consults = $consult->loadAll($ids);

CConsultation::massLoadFwdRef($consults, 'sejour_id');
CConsultation::massLoadFwdRef($consults, 'plageconsult_id');

$infos = array(
  'create' => 0,
  'found'  => 0,
);
/** @var CConsultation $_consult */
foreach ($consults as $_consult) {
  $_consult->loadRefSejour();

  if (!$_consult->_ref_sejour || !$_consult->_ref_sejour->_id) {
    $_consult->_force_create_sejour = 1;

    if ($msg = $_consult->store()) {
      CAppUI::setMsg($msg, UI_MSG_WARNING);
    }
    else {
      $infos['create']++;
    }
  }
  else {
    $infos['found']++;
  }
}

CAppUI::setMsg('CConsultation-Create sejours count', UI_MSG_OK, $infos['create']);
CAppUI::setMsg('CConsultation-Found sejours count', UI_MSG_OK, $infos['found']);

echo CAppUI::getMsg();
CApp::rip();