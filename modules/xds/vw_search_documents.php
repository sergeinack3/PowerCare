<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\Xds\CXDSFile;
use Ox\Interop\Xds\CXDSRequest;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$types_code      = CXDSFile::getTypeDocument();
$patient         = new CPatient();
$receivers_hl7v3 = CXDSRequest::getDocumentRegistry(false);

$smarty = new CSmartyDP();
$smarty->assign("types_code", $types_code);
$smarty->assign("patient", $patient);
$smarty->assign("receivers_hl7v3", $receivers_hl7v3);
$smarty->display("vw_search_documents.tpl");