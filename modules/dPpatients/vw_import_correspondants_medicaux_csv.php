<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Patients\CCSVImportCorrespondantMedical;
use Ox\Mediboard\Patients\CMedecin;

CCanDo::checkAdmin();

$medecin = new CMedecin();

$smarty = new CSmartyDP();
$smarty->assign('specs', $medecin->getPlainProps());
$smarty->assign('fields', CCSVImportCorrespondantMedical::$FIELDS);
$smarty->display('vw_import_correspondants_medicaux_csv');
