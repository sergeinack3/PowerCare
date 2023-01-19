<?php
/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Entity;

use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Description
 */
class CDiplomeAutorisationExercice extends CAbstractExternalRppsObject {
  public const MEDECIN_SYNC_FIELDS = [];

  /** @var int */
  public $diplome_exercice_autorisation_id;

  /** @var string */
  public $code_type_diplome;

  /** @var string */
  public $libelle_type_diplome;

  /** @var string */
  public $code_diplome;

  /** @var string */
  public $libelle_diplome;

  /** @var string */
  public $code_type_autorisation;

  /** @var string */
  public $libelle_type_autorisation;

  /** @var string */
  public $code_discipline_autorisation;

  /** @var string */
  public $libelle_discipline_autorisation;

  /**
   * @inheritdoc
   */
  function getSpec(): CMbObjectSpec {
    $spec           = parent::getSpec();
    $spec->table    = "diplome_autorisation_exercice";
    $spec->key      = 'diplome_autorisation_exercice_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps(): array {
    $props = parent::getProps();

    $props['code_type_diplome']               = 'str';
    $props['libelle_type_diplome']            = 'str';
    $props['code_diplome']                    = 'str';
    $props['libelle_diplome']                 = 'str';
    $props['code_type_autorisation']          = 'str';
    $props['libelle_type_autorisation']       = 'str';
    $props['code_discipline_autorisation']    = 'str';
    $props['libelle_discipline_autorisation'] = 'str';

    return $props;
  }

  /**
   * @inheritDoc
   */
  public function synchronize(?CMedecin $medecin = null): CMedecin {
    if (!$medecin) {
      $medecin = new CMedecin();
    }
    // TODO Synchronize fields
    // code_discipline_autorisation && libelle_discipline_autorisation always empty
    return $medecin;
  }
}