<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Urgences\CMotifSFMU;

CView::checkin();

$request = new CRequest();
$request->addSelect("categorie");
$request->addTable("motif_sfmu");
$request->addGroup("categorie");
$query = $request->makeSelect();

$motif_sfmu = new CMotifSFMU();
$ds         = $motif_sfmu->getDS();
$categories = $ds->loadList($query);

$smarty = new CSmartyDP();
$smarty->assign("categories", $categories);
$smarty->display("inc_search_motif_sfmu");