<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Exception;
use Ox\Core\CMbObject;

/**
 * A supervision graph
 */
class CSupervisionGraphToPack extends CMbObject {
  public $supervision_graph_to_pack_id;

  public $graph_class;
  public $graph_id;

  public $pack_id;

  public $rank;

  /** @var CSupervisionTimedEntity */
  public $_ref_graph;

  /** @var CSupervisionGraphPack */
  public $_ref_pack;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                   = parent::getSpec();
    $spec->table            = "supervision_graph_to_pack";
    $spec->key              = "supervision_graph_to_pack_id";
    $spec->uniques["title"] = array("graph_class", "graph_id", "pack_id");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props["graph_class"] = "enum list|CSupervisionGraph|CSupervisionTimedData|CSupervisionTimedPicture|CSupervisionInstantData|CSupervisionTable";
    $props["graph_id"]    = "ref notNull class|CSupervisionTimedEntity meta|graph_class cascade back|pack_links";
    $props["pack_id"]     = "ref notNull class|CSupervisionGraphPack back|graph_links";
    $props["rank"]        = "num notNull";

    return $props;
  }

  /**
   * Load graph to pack object by his rank
   *
   * @param int $rank Rank object
   *
   * @return CSupervisionGraphToPack
   * @throws Exception
   */
  function loadGraphToPackByRank($rank) {
    $where            = array();
    $where["pack_id"] = " = '$this->pack_id'";
    $where["rank"]    = " = '$rank'";

    $graph_to_pack = new self();

    $graph_to_pack->loadObject($where);

    return $graph_to_pack;
  }

  /**
   * Get the graph
   *
   * @return CSupervisionTimedEntity|CSupervisionGraph|CSupervisionTimedData|CSupervisionTimedPicture
   * @throws Exception
   */
  function loadRefGraph() {
    return $this->_ref_graph = $this->loadFwdRef("graph_id");
  }

  /**
   * Get the pack
   *
   * @return CSupervisionGraphPack
   * @throws Exception
   */
  function loadRefPack() {
    return $this->_ref_pack = $this->loadFwdRef("pack_id");
  }
}
