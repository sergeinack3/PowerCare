<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\Module\CModule;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Courrier\CPliPostal;
use Ox\Mediboard\Docapost\CDocapost;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\Mssante\CMSSanteMail;

/**
 * Description
 */
class CLinkDestDispatch extends CStoredObject {
  /**
   * @var integer Primary key
   */
  public $link_dest_dispatch_id;

  // DB Fields
  public $destinataire_item_id;
  public $dispatch_class;
  public $dispatch_id;

  // References
  /** @var CDestinataireItem */
  public $_ref_destinataire;

  /** @var CPliPostal|CUserMail|CMSSanteMail */
  public $_ref_dispatch;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "link_dest_dispatch";
    $spec->key   = "link_dest_dispatch_id";

    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props                         = parent::getProps();
    $props["destinataire_item_id"] = "ref class|CDestinataireItem notNull back|links_dispatches";

    $dispatch_class = "CUserMail";

    if (class_exists("CMSSanteMail")) {
      $dispatch_class .= "|CMSSanteMail";
    }

    if (class_exists("CPliPostal")) {
      $dispatch_class .= "|CPliPostal";
    }

    $props["dispatch_class"] = "enum list|$dispatch_class";
    $props["dispatch_id"]    = "ref notNull class|CMbObject meta|dispatch_class back|links";

    return $props;
  }

  /**
   * Charge le destinataire
   *
   * @return CDestinataireItem
   */
  function loadRefDestinataire() {
    return $this->_ref_destinataire = $this->loadFwdRef("destinataire_item_id", true);
  }

  /**
   * Charge l'élément associé à l'envoi
   * @return CDocapost|CUserMail|CMSSanteMail
   */
  function loadRefDispatch() {
    return $this->_ref_dispatch = $this->loadFwdRef("dispatch_id", true);
  }
}
