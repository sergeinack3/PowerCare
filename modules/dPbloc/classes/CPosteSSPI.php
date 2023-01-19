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
 * Poste de SSPI (lit en salle de reveil)
 * Class CPosteSSPI
 */
class CPosteSSPI extends CMbObject {
  public $poste_sspi_id;

  // DB References
  public $sspi_id;

  // DB Fields
  public $nom;
  public $type;
  public $actif;

  /** @var CBlocOperatoire */
  public $_ref_bloc;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "poste_sspi";
    $spec->key   = "poste_sspi_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props            = parent::getProps();
    $props["sspi_id"] = "ref class|CSSPI back|postes_sspi";
    $props["nom"]     = "str notNull seekable";
    $props["type"]    = "enum list|sspi|preop default|sspi";
    $props["actif"]   = "bool notNull default|1";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->nom;
  }

  /**
   * Chargement du bloc opératoire concerné
   *
   * @return CBlocOperatoire
   */
  function loadRefBloc() {
    return $this->_ref_bloc = $this->loadFwdRef("bloc_id");
  }
}
