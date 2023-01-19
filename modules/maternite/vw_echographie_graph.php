<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CEchoGraph;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CSurvEchoGrossesse;

CCanDo::checkRead();
$grossesse_id         = CView::get('grossesse_id', 'ref class|CGrossesse notNull');
$graph_name           = CView::get('graph_name', 'str notNull');
$graph_size           = CView::get('graph_size', 'str notNull');
$num_enfant           = CView::get('num_enfant', 'num');
$show_select_children = CView::get('show_select_children', 'bool');
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);

$survEchoList = array();
$survEchoData = array();
$echo_children = array();

$echographies  = $grossesse->loadRefsSurvEchographies();

if ($grossesse->multiple && $num_enfant) {
  foreach ($echographies as $key_echo => $_echographie) {
    $echo_children[$_echographie->num_enfant] = $_echographie->num_enfant;

    if ($num_enfant != $_echographie->num_enfant) {
      unset($echographies[$key_echo]);
    }
  }
}

/** @var CSurvEchoGrossesse $_echographie */
foreach ($echographies as $_echographie) {
  $age_gest = $grossesse->getAgeGestationnel($_echographie->date);
  $x = $age_gest['SA'];

  if ($graph_name === 'cn') {
    $x = $age_gest['SA']*7 + $age_gest['JA'];
  }

  if ($grossesse->multiple && $_echographie->num_enfant) {
    $echo_children[$_echographie->num_enfant] = $_echographie->num_enfant;
    $survEchoList[$_echographie->num_enfant][] = array($x, $_echographie->$graph_name, 'id' => $_echographie->_id);
  }
  else {
    $survEchoList[] = array($x, $_echographie->$graph_name, 'id' => $_echographie->_id);
  }
}

if ($grossesse->multiple) {
  /** @var CSurvEchoGrossesse $_echographie */
  foreach ($echographies as $_echographie) {
    $survEchoData[$_echographie->num_enfant] = array(
      'id'     => "{$grossesse_id}",
      'label'  => "Enfant n°" . $_echographie->num_enfant,
      'data'   => $survEchoList[$_echographie->num_enfant],
      'lines'  => array('show' => true),
      'points' => array('show' => true),
    );
  }
}
else {
  $survEchoData[] = array(
    'id'     => "{$grossesse_id}",
    'label'  => 'Enfant',
    'data'   => $survEchoList,
    'lines'  => array('show' => true),
    'points' => array('show' => true),
    'color'  => 'rgb(0,0,0)',
  );
}

$smarty = new CSmartyDP();
$smarty->assign('graph_axes'          , CEchoGraph::formatGraphDataset($graph_name));
$smarty->assign('graph_name'          , $graph_name);
$smarty->assign('survEchoData'        , $survEchoData);
$smarty->assign('graph_size'          , $graph_size);
$smarty->assign('echographies'        , $echo_children);
$smarty->assign('num_enfant'          , $num_enfant);
$smarty->assign('grossesse'           , $grossesse);
$smarty->assign('show_select_children', $show_select_children);
$smarty->display('vw_echographie_graph');
