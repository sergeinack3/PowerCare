<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Interop\Eai\CExchangeTransportLayer;

/**
 * FileSystem exchange
 */
class CExchangeFileSystem extends CExchangeTransportLayer {
  public $exchange_fs_id;

  /** @var CSourceFileSystem */
  public $_source;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->loggable = false;
    $spec->table    = 'exchange_fs';
    $spec->key      = 'exchange_fs_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["source_class"]  = "enum list|CSourceFileSystem";
    $props["source_id"]  .= " cascade back|echange_fs";
    return $props;
  }
}