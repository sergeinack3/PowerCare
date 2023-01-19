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
 * XML content
 */
class CContentXML extends CMbObject {
  public $content_id;
  
  // DB Fields
  public $content;
  public $import_id;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'content_xml';
    $spec->key   = 'content_id';
    $spec->loggable = false;
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() { 
    $props = parent::getProps();
    $props["content"]   = "xml show|0";
    $props["import_id"] = "num";
    return $props;
  }
}
