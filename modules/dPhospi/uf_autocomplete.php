<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;

CCanDo::checkRead();

$input_field = CView::get('input_field', 'str');
$libelle     = trim(CView::get($input_field, 'str'));
$type        = CView::get("type", "str");
$group_id    = CView::get("group_id", "ref class|CGroups");
$entree      = CView::get("entree", "date");
$sortie      = CView::get("sortie", "date");

CView::checkin();

CView::enableSlave();

$unite_fonctionnelle = new CUniteFonctionnelle();
$ds                  = $unite_fonctionnelle->getDS();

$where = [
    $ds->prepare("type = ?", $type),
    $ds->prepare("group_id = ?", $group_id),
];

if ($entree) {
    $where[] = $ds->prepare("date_debut IS NULL OR date_debut <= $entree");
}
if ($sortie) {
    $where[] = $ds->prepare("date_fin IS NULL OR date_fin >= $sortie");
}

$matches  = $unite_fonctionnelle->getAutocompleteList($libelle, $where, 50);
$template = $unite_fonctionnelle->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");

$smarty->assign("matches", $matches);
$smarty->assign('view_field', "code");
$smarty->assign('field', null);
$smarty->assign('show_view', true);
$smarty->assign("nodebug", true);
$smarty->assign('template', $template);

$smarty->display("inc_field_autocomplete");
