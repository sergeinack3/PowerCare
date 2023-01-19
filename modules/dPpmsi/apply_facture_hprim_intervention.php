<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkAdmin();

CAppUI::stepAjax("Fonctionnalité désactivée pour le moment", UI_MSG_ERROR);
return;

$operation = new COperation();

$operation->facture = "1";
$count = $operation->countMatchingList();
CAppUI::stepAjax("'%s' opérations facturées trouvées", UI_MSG_OK, $count);
$operation->facture = "0";
$count = $operation->countMatchingList();
CAppUI::stepAjax("'%s' opérations non facturées trouvées", UI_MSG_OK, $count);

$start = 30000;
$max = 100;
$limit = "$start, $max";

/** @var COperation $_operation */
foreach ($operation->loadMatchingList(null, $limit) as $_operation) {
  $_operation->loadHprimFiles();
  if ($count = count($_operation->_ref_hprim_files)) {
    CAppUI::stepAjax("'%s' HPRIM files for operation '%s'", UI_MSG_OK, $count, $_operation->_view);
  }
}
