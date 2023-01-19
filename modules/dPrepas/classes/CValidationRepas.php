<?php
/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Repas;

use Ox\Core\CMbObject;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;

/**
 * The CValidationRepas class
 */
class CValidationRepas extends CMbObject {
  // DB Table key
  public $validationrepas_id;

  // DB Fields
  public $service_id;
  public $date;
  public $typerepas_id;
  public $modif;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'validationrepas';
    $spec->key   = 'validationrepas_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specsParent = parent::getProps();
    $specs       = array(
      "service_id"   => "ref notNull class|CService back|valid_repas",
      "date"         => "date",
      "typerepas_id" => "ref notNull class|CTypeRepas back|valid_repas",
      "modif"        => "bool"
    );

    return array_merge($specsParent, $specs);
  }

  function resetModifications() {

    $typeRepas = new CTypeRepas;
    $typeRepas->load($this->typerepas_id);
    $service = new CService();
    $service->load($this->service_id);
    $service->loadRefsBack();
    foreach ($service->_ref_chambres as $chambre_id => &$chambre) {
      $chambre->loadRefsBack();
      CLit::massCheckDispo($chambre->_ref_lits, $this->date);
      foreach ($chambre->_ref_lits as $lit_id => &$lit) {
        $lit->loadAffectations($this->date);
        foreach ($lit->_ref_affectations as $affectation_id => &$affectation) {
          $affectation->loadRefSejour();
          $affectation->loadMenu($this->date, array($this->typerepas_id => null));
          $sejour =& $affectation->_ref_sejour;

          $date_entree  = substr($affectation->entree, 0, 10);
          $date_sortie  = substr($affectation->sortie, 0, 10);
          $heure_entree = substr($affectation->entree, 11, 5);
          $heure_sortie = substr($affectation->sortie, 11, 5);

          if (!$sejour->sejour_id || $sejour->type == "ambu" ||
            ($this->date == $date_entree && $heure_entree > $typeRepas->fin) ||
            ($this->date == $date_sortie && $heure_sortie < $typeRepas->debut)) {
          }
          else {
            $repas =& $affectation->_list_repas[$this->date][$this->typerepas_id];

            if ($repas->modif) {
              $repas->modif       = 0;
              $repas->_no_synchro = true;
              $repas->store();
            }
          }
        }
      }
    }
  }
}