<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CMbObject;

/**
 * Groupe de séance d'une consultation
 */
class CGroupeSeance extends CMbObject {
  public $groupe_seance_id;

  // DB References
  public $patient_id;
  public $function_id; // cabinet concerné
  public $category_id; // catégorie de consultation

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'groupe_seance';
    $spec->key   = 'groupe_seance_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props["patient_id"]  = "ref notNull class|CPatient back|groupe_seance";
    $props["function_id"] = "ref notNull class|CFunctions back|groupe_seance";
    $props["category_id"] = "ref notNull class|CConsultationCategorie back|groupe_seance";

    return $props;
  }
}
