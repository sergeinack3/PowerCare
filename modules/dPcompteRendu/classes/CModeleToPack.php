<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\CStoredObject;

/**
 * Lien vers un modèle, composant d'un pack
 */
class CModeleToPack extends CStoredObject {
  // DB Table key
  public $modele_to_pack_id;

  // DB References
  public $modele_id;
  public $pack_id;
  public $is_selected;

  /** @var CCompteRendu */
  public $_ref_modele;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'modele_to_pack';
    $spec->key   = 'modele_to_pack_id';
    $spec->uniques['document'] = array('modele_id', 'pack_id');
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["modele_id"]   = "ref class|CCompteRendu back|pack_links";
    $props["pack_id"]     = "ref class|CPack cascade back|modele_links";
    $props["is_selected"] = "bool default|0";
    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->loadRefModele()->nom;
  }

  /**
   * Chargement du modèle référencé
   *
   * @return CCompteRendu
   */
  function loadRefModele() {
    return $this->_ref_modele = $this->loadFwdRef("modele_id", true);
  }

  /**
   * Charge tous les liens vers les modèles que composent un pack
   *
   * @param object $pack_id identifiant du pack
   *
   * @return array
   */
  function loadAllModelesFor($pack_id) {
    $where = array();
    $where["pack_id"] = "= '$pack_id'";

    return $this->loadList($where);
  }
}
