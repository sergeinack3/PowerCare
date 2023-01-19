<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;

/**
 * Lien unique entre un bloc et une SSPI
 */
class CSSPILink extends CStoredObject {
  /** @var integer Primary key */
  public $sspi_link_id;

  // DB Fields
  public $bloc_id;
  public $sspi_id;

  // References
  /** @var CBlocOperatoire */
  public $_ref_bloc;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "sspi_link";
    $spec->key   = "sspi_link_id";
    $spec->uniques["bloc_sspi"] = array("bloc_id", "sspi_id");
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["bloc_id"] = "ref class|CBlocOperatoire notNull back|links_sspi";
    $props["sspi_id"] = "ref class|CSSPI notNull back|links_sspi";
    return $props;
  }

  /**
   * Charge le bloc opératoire associé au lien
   *
   * @return CBlocOperatoire
   */
  function loadRefBloc() {
    return $this->_ref_bloc = $this->loadFwdRef("bloc_id", true);
  }

  /**
   * Charge les SSPIs attachées à un bloc
   *
   * @param int $bloc_id Identifiant du bloc
   *
   * @return CSSPI[]
   */
  static function getSSPIsForBloc($bloc_id) {
    $sspi_link = new self();
    $sspi_link->bloc_id = $bloc_id;

    $sspis_ids = $sspi_link->loadColumn("sspi_id");

    $sspi = new CSSPI();

    return $sspi->loadList(array("sspi_id" => CSQLDataSource::prepareIn($sspis_ids)));
  }
}
