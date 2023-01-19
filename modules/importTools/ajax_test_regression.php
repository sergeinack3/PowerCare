<?php 
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Import\ImportTools\CRegressionTester;

CCanDo::checkAdmin();

$import_tag = CView::get('import_tag', 'str');
$nb_tests = CView::get('nb_import_tests', 'num default|1000');

CView::checkin();

$regression_test = new CRegressionTester($nb_tests);
$regression_test->setImportTag($import_tag);

$total = 0;
$msg = '';
if (!$regression_test->import_tag) {
  $msg = CAppUI::tr('mod-importTools-import-tag-none');
}
else {
  $regression_test->getRegression();
  $regression_test->countEverything();

  $total = $regression_test->counts['miss']['all'] + $regression_test->counts['same']['all']
    + $regression_test->counts['diff']['all'];

}


$smarty = new CSmartyDP();
$smarty->assign('regression', $regression_test->result_regression);
$smarty->assign('counts', $regression_test->counts);
$smarty->assign('classes', array_keys($regression_test->result_regression['miss']));
$smarty->assign('total', $total);
$smarty->assign('msg', $msg);
$smarty->assign('tag', $import_tag);

$smarty->display('inc_result_regression.tpl');