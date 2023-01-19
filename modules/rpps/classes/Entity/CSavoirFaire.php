<?php
/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Entity;

use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Import\Rpps\CExternalMedecinBulkImport;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Description
 */
class CSavoirFaire extends CAbstractExternalRppsObject {
  public const MEDECIN_SYNC_FIELDS = [];

  /** @var int */
  public $savoir_faire_id;

  /** @var string */
  public $code_profession;

  /** @var string */
  public $libelle_profession;

  /** @var string */
  public $code_categorie_pro;

  /** @var string */
  public $libelle_categorie_pro;

  /** @var string */
  public $code_type_savoir_faire;

  /** @var string */
  public $libelle_type_savoir_faire;

  /** @var string */
  public $code_savoir_faire;

  /** @var string */
  public $libelle_savoir_faire;

  /**
   * @inheritdoc
   */
  function getSpec(): CMbObjectSpec {
    $spec           = parent::getSpec();

    $spec->table    = "savoir_faire";
    $spec->key      = "savoir_faire_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps(): array {
    $props = parent::getProps();

    $props['code_profession']           = 'str notNull';
    $props['libelle_profession']        = 'str notNull';
    $props['code_categorie_pro']        = 'str';
    $props['libelle_categorie_pro']     = 'str';
    $props['code_type_savoir_faire']    = 'str';
    $props['libelle_type_savoir_faire'] = 'str';
    $props['code_savoir_faire']         = 'str';
    $props['libelle_savoir_faire']      = 'str';

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
    return $medecin;
  }
}