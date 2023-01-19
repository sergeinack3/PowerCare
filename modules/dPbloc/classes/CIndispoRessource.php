<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;

/**
 * Plage d'indisponibilité de ressources materielles
 * Class CIndispoRessource
 */
class CIndispoRessource extends CStoredObject {
  public $indispo_ressource_id;

  // DB References
  public $ressource_materielle_id;

  // DB Fields
  public $deb;
  public $fin;
  public $commentaire;

  /** @var CRessourceMaterielle */
  public $_ref_ressource_materielle;

  // Form Fields
  public $_debut_offset;
  public $_fin_offset;
  public $_width;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'indispo_ressource';
    $spec->key   = 'indispo_ressource_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["ressource_materielle_id"] = "ref notNull class|CRessourceMaterielle autocomplete|libelle back|indispos";
    $props["deb"]                     = "dateTime notNull";
    $props["fin"]                     = "dateTime notNull";
    $props["commentaire"]             = "text helped";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = "Indisponibilité du " . CMbDT::dateToLocale($this->deb);
    if ($this->deb != $this->fin) {
      $this->_view .= " au " . CMbDT::dateToLocale($this->fin);
    }
  }

  /**
   * Chargement de la ressource materielle correspondante
   *
   * @return CRessourceMaterielle
   */
  function loadRefRessource() {
    return $this->_ref_ressource_materielle = $this->loadFwdRef("ressource_materielle_id", true);
  }
}
