<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;

CCanDo::checkAdmin();

$bloc_id = CView::get("bloc_id", "ref class|CBlocOperatoire");

CView::checkin();

// Récupération du bloc à ajouter / modifier
$bloc = new CBlocOperatoire();
$bloc->load($bloc_id);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("bloc", $bloc);

$smarty->display("inc_edit_bloc");
