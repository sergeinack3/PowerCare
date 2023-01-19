<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Ssr\CReplacement;

CCanDo::checkAdmin();

$replacement = new CReplacement;
/** @var CReplacement[] $replacements */
$replacements = $replacement->loadList();
foreach ($replacements as $_replacement) {
  $_replacement->loadDates();
  $_replacement->_ref_conge->loadRefUser();
  $_replacement->_ref_sejour->loadRefPatient();
  $count = $_replacement->checkCongesRemplacer();
  if (!$count) {
    unset($replacements[$_replacement->_id]);
    continue;
  }

  $replacer = $_replacement->loadRefReplacer();
  $replacer->loadRefFunction();

  $_replacement->makeFragments();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("replacements", $replacements);
$smarty->display("inc_check_replacements");
