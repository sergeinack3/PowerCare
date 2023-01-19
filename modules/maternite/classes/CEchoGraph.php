<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Echography graph representation
 */
class CEchoGraph implements IShortNameAutoloadable {

  /**
   * Format graph axes according to flot format
   *
   * @param string $graph_name One of lcc|bip|pc|dat|pa|lf
   *
   * @return array
   */
  static function formatGraphDataset($graph_name) {
    if ($graph_name == 'lcc') {
      return array(CSurvEchoGrossesse::$graph_axes[$graph_name]);
    }
    $dataset = array();
    $series  = array();
    foreach (CSurvEchoGrossesse::$graph_axes[$graph_name] as $_sa => $_values) {
      $series["3%"][]  = array($_sa, $_values[0]);
      $series['10%'][] = array($_sa, $_values[1]);
      $series['50%'][] = array($_sa, $_values[2]);
      $series['90%'][] = array($_sa, $_values[3]);
      $series['97%'][] = array($_sa, $_values[4]);
    }

    $dataset[] = array(
      'id'        => "{$graph_name}_3%",
      'data'      => $series['3%'],
      'lines'     => array(
        'show'      => true,
        'lineWidth' => 0.5
      ),
      'points'    => array('show' => false),
      'color'     => 'rgb(70,130,180)',
      'hoverable' => false,
      'clickable' => false,
    );
    $dataset[] = array(
      'id'          => "{$graph_name}_10%",
      'data'        => $series['10%'],
      'lines'       => array(
        'show'      => true,
        'lineWidth' => 0,
        'fill'      => 0.4
      ),
      'points'      => array('show' => false),
      'color'       => 'rgb(255,50,50)',
      'fillBetween' => "{$graph_name}_50%",
      'hoverable'   => false,
      'clickable'   => false,
    );
    $dataset[] = array(
      'id'        => "{$graph_name}_50%",
      'data'      => $series['50%'],
      'lines'     => array(
        'show'      => true,
        'lineWidth' => 1
      ),
      'points'    => array('show' => false),
      'color'     => 'rgb(70,130,180)',
      'hoverable' => false,
      'clickable' => false,
    );
    $dataset[] = array(
      'id'          => "{$graph_name}_90%",
      'data'        => $series['90%'],
      'lines'       => array(
        'show'      => true,
        'lineWidth' => 0,
        'fill'      => 0.4
      ),
      'points'      => array('show' => false),
      'color'       => 'rgb(255,50,50)',
      'fillBetween' => "{$graph_name}_50%",
      'hoverable'   => false,
      'clickable'   => false,
    );
    $dataset[] = array(
      'id'        => "{$graph_name}_97%",
      'data'      => $series['97%'],
      'lines'     => array(
        'show'      => true,
        'lineWidth' => 0.5
      ),
      'points'    => array('show' => false),
      'color'     => 'rgb(70,130,180)',
      'hoverable' => false,
      'clickable' => false,
    );

    return $dataset;
  }
}
