<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 */
class CHyperTextLink extends CMbObject {
  public const RESOURCE_TYPE = 'hyperlink';

  /**
   * @var integer Primary key
   */
  public $hypertext_link_id;

  /**
   * The name of the link
   *
   * @var string
   */
  public $name;

  /**
   * The hypertext link
   *
   * @var string
   */
  public $link;

  public $object_class;
  public $object_id;
  public $_ref_object;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "hypertext_link";
    $spec->key   = "hypertext_link_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props              = parent::getProps();
    $props["object_id"] = "ref notNull class|CMbObject meta|object_class cascade back|hypertext_links fieldset|default";
    $props["object_class"] = "str notNull class show|0 fieldset|default";
    $props["name"]      = "str notNull fieldset|default";
    $props["link"]      = "uri notNull fieldset|default";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->name;
  }


  /**
   * @param CStoredObject $object
   * @deprecated
   * @todo redefine meta raf
   * @return void
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @param bool $cache
   * @deprecated
   * @todo redefine meta raf
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }
}
