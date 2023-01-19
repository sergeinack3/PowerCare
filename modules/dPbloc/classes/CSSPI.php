<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;

/**
 * Gestion des SSPI au sein des blocs
 */
class CSSPI extends CMbObject {
  /** @var integer Primary key */
  public $sspi_id;

  // DB Fields
  public $libelle;
  public $group_id;

  // References
  /** @var CBlocOperatoire[] */
  public $_ref_blocs;

  /** @var CPosteSSPI[] */
  public $_ref_postes;

  /** @var CSSPILink */
  public $_ref_sspis_links;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "sspi";
    $spec->key   = "sspi_id";
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["libelle"]  = "str notNull";
    $props["group_id"] = "ref class|CGroups back|links_sspi";
    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->libelle;
  }

  /**
   * Chargement des blocs
   *
   * @return CBlocOperatoire[]
   */
  public function loadRefsBlocs() {
    $this->_ref_sspis_links = $this->loadBackRefs("links_sspi");

    $this->_ref_blocs = CStoredObject::massLoadFwdRef($this->_ref_sspis_links, "bloc_id");

    foreach ($this->_ref_sspis_links as $_sspi_link) {
      $_sspi_link->loadRefBloc();
    }

    return $this->_ref_blocs;
  }

  /**
   * Chargement des postes
   *
   * @return CPosteSSPI[]
   */
  public function loadRefsPostes() {

      $postes = $this->loadBackRefs("postes_sspi", "nom");
      /** @var CPosteSSPI[] $postes */
      $postes = self::naturalSort($postes,['nom']);
      return $this->_ref_postes = $postes;
  }
}
