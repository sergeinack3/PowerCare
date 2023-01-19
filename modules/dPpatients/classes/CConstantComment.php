<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;

/**
 * Description
 */
class CConstantComment extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $constant_comment_id;

  /**
   * @var string The comment
   */
  public $comment;

  /**
   * @var string The name of the constant linked to the comment
   */
  public $constant;

  /**
   * @var integer The id of the CConstantesMedicales linked to the comment
   */
  public $constant_id;

  /**
   * @var CConstantesMedicales The linked CConstantesMedicales
   */
  public $_ref_constant;

  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec                      = parent::getSpec();
    $spec->table               = 'constant_comments';
    $spec->key                 = 'constant_comment_id';
    $spec->uniques['constant'] = array('constant', 'constant_id');

    return $spec;
  }


  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props['comment']     = 'text notNull helped';
    $props['constant']    = 'str notNull';
    $props['constant_id'] = 'ref class|CConstantesMedicales notNull back|comments cascade';

    return $props;
  }

  public function loadRefConstantes($cache = true) {
    return $this->_ref_constant = $this->loadFwdRef('constant_id', $cache);
  }
}
