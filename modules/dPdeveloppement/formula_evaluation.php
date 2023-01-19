<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbMath;
use Ox\Core\CSmartyDP;

CCanDo::checkRead();

$expressions = array(
  '1+2'                              => 3,
  '$a+$b'                            => 3,
  '$a+$b * ($c - $e)'                => -13,
  'floor($a * 10 / 4)'               => 2,
  'cos($a * 10 / 4)'                 => -0.80114361554693,
  'Min(1058087)'                     => 18,
  'J(1502803159000 - 1501593559000)' => 14,
);

$variables = array(
  'a' => 1,
  'b' => 2,
  'c' => 3,
  'd' => 4,
  'e' => 10,
);

$server_side = array();

foreach ($expressions as $_expression => $_result) {
  $server_side[$_expression] = CMbMath::evaluate($_expression, $variables);
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("expressions", $expressions);
$smarty->assign("variables", $variables);
$smarty->assign("server_side", $server_side);
$smarty->display('formula_evaluation.tpl');
