<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CMbObject;

/**
 * Tabular content
 */
class CContentTabular extends CMbObject {
  public $content_id;
  
  // DB Fields
  public $content;
  public $import_id;
  public $separator;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'content_tabular';
    $spec->key   = 'content_id';
    $spec->loggable = false;
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() { 
    $props = parent::getProps();
    $props["content"]   = "text show|0";
    $props["import_id"] = "num";
    $props["separator"] = "str length|1";
    
    return $props;
  }
}
