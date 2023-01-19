<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CColorLibelleSejour;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

CView::checkin();

// Tous les libelllés
$query = new CRequest();
$query->addTable("sejour");
$query->addColumn("COUNT(libelle)", "libelle_count");
$query->addColumn("libelle");
$query->addWhereClause("type", "= 'ssr'");
$query->addOrder("libelle_count DESC");
$query->addGroup("libelle");

$sejour = new CSejour();
$ds = $sejour->getDS();
$libelle_counts = array();
foreach ($ds->loadList($query->makeSelect()) as $row) {
  $libelle_counts[$row["libelle"]] = $row["libelle_count"];
}
unset($libelle_counts[""]);

// Libellés disponibles
$colors = CColorLibelleSejour::loadAllFor(array_keys($libelle_counts));

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("libelle_counts", $libelle_counts);
$smarty->assign("colors"        , $colors);

$smarty->display("vw_idx_colors");
