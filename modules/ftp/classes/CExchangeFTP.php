<?php
/**
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ftp;

use Ox\Core\CAppUI;
use Ox\Interop\Eai\CExchangeTransportLayer;

/**
 * Class CExchangeFTP
 */
class CExchangeFTP extends CExchangeTransportLayer {
  // DB Table key
  public $echange_ftp_id;
  
  // DB Fields
  public $ftp_fault;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->loggable = false;
    $spec->table = 'echange_ftp';
    $spec->key   = 'echange_ftp_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["ftp_fault"]     = "bool";
    $props["source_id"]     .= " back|echange_ftp cascade";
    $props["source_class"]  = "enum list|CSourceFTP";
    return $props;
  }

  /**
   * @inheritdoc
   */
  function unserialize() {
    $this->input  = unserialize($this->input);
    if ($this->ftp_fault != 1) {
      $this->output = unserialize($this->output);
    }
  }

  /**
   * @inheritdoc
   */
  function fillDownloadExchange() {
    $content = parent::fillDownloadExchange();

    $output = $this->ftp_fault ? print_r($this->output, true) : print_r(unserialize($this->output), true);
    $content .= CAppUI::tr("{$this->_class}-output") . " : {$output} \n \n";

    return $content;
  }
}