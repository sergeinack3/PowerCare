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
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();
$parent_id = CView::get("parent_id", "ref class|CPatient");
CView::checkin();

$patient = new CPatient();
$patient->load($parent_id);

$coordonnees = array();

$coordonnees["adresse"] = $patient->adresse;
$coordonnees["cp"]      = $patient->cp;
$coordonnees["ville"]   = $patient->ville;
$coordonnees["pays"]    = $patient->pays;

CApp::json($coordonnees);
