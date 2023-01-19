<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Ssr\CPlateauTechnique;

global $m;
CCanDo::checkRead();

$where    = array("type = '$m' OR type IS NULL");
$plateau  = new CPlateauTechnique();
$plateaux = $plateau->loadGroupList($where);

// Plateaux disponibles
$plateaux_ids = array();
foreach ($plateaux as $_plateau) {
  $equipements                  = $_plateau->loadBackRefs("equipements");
  $plateaux_ids[$_plateau->_id] = array_keys($equipements);
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("plateaux", $plateaux);
$smarty->assign("plateaux_ids", $plateaux_ids);
$smarty->display("vw_plateau_board");
