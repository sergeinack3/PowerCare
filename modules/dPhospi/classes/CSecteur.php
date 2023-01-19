<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

/**
 * Secteur d'établissement, regroupe des services
 */
class CSecteur extends CInternalStructure {
  // DB Table key
  public $secteur_id;

  // DB references
  public $group_id;

  // DB Fields
  public $nom;
  public $description;

  /** @var CService[] */
  public $_ref_services;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'secteur';
    $spec->key   = 'secteur_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                = parent::getProps();
    $props["user_id"]    .= " back|secteurs";
    $props["group_id"]    = "ref notNull class|CGroups back|secteurs";
    $props["nom"]         = "str notNull";
    $props["description"] = "text seekable";

    return $props;
  }

  /**
   * @see parent::udpateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->nom;
  }

  /**
   * Charge les services
   *
   * @return CService[]
   */
  function loadRefsServices() {
    return $this->_ref_services = $this->loadBackRefs("services", "nom");
  }

  /**
   * @see parent::mapEntityTo()
   */
  function mapEntityTo() {
    $this->_name = $this->nom;
  }

  /**
   * @see parent::mapEntityFrom()
   */
  function mapEntityFrom() {
    if ($this->_name != null) {
      $this->nom = $this->_name;
    }
  }
}