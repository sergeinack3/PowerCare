<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CMbObject;
use Ox\Mediboard\CompteRendu\CCompteRendu;

/**
 * Description
 */
class CContextDoc extends CMbObject {
  /** @var integer Primary key */
  public $context_doc_id;
  public $context_id;
  public $context_class;
  public $type;

  // References
  /** @var CCompteRendu[] */
  public $_ref_documents = [];
  public $_count_docs;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "context_doc";
    $spec->key    = "context_doc_id";
    $spec->uniques["type"] = array("context_class", "context_id", "type");
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["context_id"]    = "ref notNull class|CMbObject meta|context_class back|context_doc";
    $props["context_class"] = "str notNull";
    $props["type"]          = "enum list|sejour|operation";
    return $props;
  }
}
