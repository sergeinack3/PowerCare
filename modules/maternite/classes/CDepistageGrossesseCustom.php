<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\CMbObject;

/**
 * Gestion des dépistages du dossier de périnatalité
 */
class CDepistageGrossesseCustom extends CMbObject {
  // DB Table key
  public $depistage_grossesse_custom_id;

  public $depistage_grossesse_id;
  public $libelle;
  public $valeur;

  /** @var CDepistageGrossesse */
  public $_ref_depistage_grossesse;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'depistage_grossesse_custom';
    $spec->key   = 'depistage_grossesse_custom_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                           = parent::getProps();
    $props["depistage_grossesse_id"] = "ref notNull class|CDepistageGrossesse back|depistages_customs";
    $props["libelle"]                = "str";
    $props["valeur"]                 = "text";

    return $props;
  }

  /**
   * Chargement du dépistage de grossesse
   *
   * @return CDepistageGrossesse
   */
  function loadRefDepistageGrossesse() {
    return $this->_ref_depistage_grossesse = $this->loadFwdRef("depistage_grossesse_id", true);
  }
}
