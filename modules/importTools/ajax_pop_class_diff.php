<?php 
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Import\ImportTools\CRegressionTester;

CCanDo::checkAdmin();

$class_name = CView::get('class_name', 'str notNull');
$tag = CView::get('tag', 'str notNull');

CView::checkin();

$regression_test = new CRegressionTester();
$regression_test->setImportTag($tag);
$comp = $regression_test->compareObjectsFromClass($class_name);

$smarty = new CSmartyDP();
$smarty->assign('comp', $comp);

$smarty->display('vw_compare_class.tpl');