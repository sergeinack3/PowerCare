<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatientSignature;

CCanDo::checkAdmin();

CApp::setTimeLimit(600);
CApp::setMemoryLimit("1024M");

CView::checkin();

CView::enforceSlave();

$patient_signature = new CPatientSignature();
$patient_signature->exportDuplicates();