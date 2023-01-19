<?php
/**
 * @package Mediboard\Maternité
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkRead();

$grossesse_id  = CView::post("grossesse_id", "ref class|CGrossesse");
$sejour_id     = CView::post("sejour_id", "ref class|CSejour");
$operation_id  = CView::post("operation_id", "ref class|COperation");
$dossier_mater = CView::post("dossier_mater", "str");

CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);

$parturiente = $grossesse->loadRefParturiente();

$parts_to_print = [];

foreach ($dossier_mater as $_title => $parts) {
    foreach ($parts as $_part) {
        $status = null;

        if ($_part === "partogramme") {
            $status = "partogramme_completed";
        }

        $parts_to_print[] = CApp::fetch(
            "maternite",
            "dossier_mater_$_part",
            [
                "grossesse_id"  => $grossesse_id,
                "operation_id"  => $operation_id,
                "sejour_id"     => $sejour_id,
                "print"         => 1,
                "dialog"        => 1
            ]
        );
    }
}

$smarty = new CSmartyDP();

$smarty->assign('parts_to_print', $parts_to_print);

$smarty->display('print_dossier_mater');
