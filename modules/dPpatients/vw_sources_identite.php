<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCando;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$patient_id = CView::getRefCheckEdit('patient_id', 'ref class|CPatient');

CView::checkin();

$patient = CPatient::findOrFail($patient_id);

$patient->updateNomPaysInsee();
$patient->loadRefsSourcesIdentite(false);

foreach ($patient->_ref_sources_identite as $_source_identite) {
    $_source_identite->loadRefJustificatif();
}

$smarty = new CSmartyDP();

$smarty->assign('patient', $patient);

$smarty->display('vw_sources_identite');
