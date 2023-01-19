<?php
/**
 * @package Mediboard\Developpement\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkRead();

$pages = array(
  'regression'  => array(
    'routage',
    'css_test',
    'form_tester',
    'mutex_tester',
    'slave_tester',
    'holidays_tester',
    'formula_evaluation',
    'thumbnail_tester',
  ),
  'performance' => array(
    'cache_tester',
    'vw_performance_profiling_analyzer',
    'vw_log_parser',
    'benchmark',
  ),
  'vue' => array(
    'atomes_vue',
    'layouts_vue',
  )
);

$smarty = new CSmartyDP();
$smarty->assign('pages', $pages);
$smarty->display('vw_tests.tpl');