<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\CMbObject;

/**
 * Blocage dans le temps d'une salle de bloc
 * Class CBlocage
 */
class CBlocage extends CMbObject {
  public $blocage_id;

  // DB References
  public $salle_id;

  // DB Fields
  public $libelle;
  public $deb;
  public $fin;

  /** @var CSalle */
  public $_ref_salle;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'blocage';
    $spec->key   = 'blocage_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["salle_id"] = "ref class|CSalle notNull back|blocages";
    $props["libelle"]  = "str seekable";
    $props["deb"]      = "dateTime notNull";
    $props["fin"]      = "dateTime notNull moreEquals|deb";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = "Blocage du " . $this->getFormattedValue("deb") . " au " . $this->getFormattedValue("fin");
  }

  /**
   * Chargement de la salle concernée
   *
   * @return CSalle
   */
  function loadRefSalle() {
    return $this->_ref_salle = $this->loadFwdRef("salle_id", true);
  }
}
