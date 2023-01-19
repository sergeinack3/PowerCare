<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\CValue;

$_validation = CValue::post("_validation", null);

/**
 * Controleur de modification d'une fiche d'évènement indésirable
 * Class CDoFicheEiAddEdit
 */
class CDoFicheEiAddEdit extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CFicheEi", "fiche_ei_id");
  }

  /**
   * @inheritdoc
   */
  function doStore() {
    global $_validation, $m;


    if (!$_validation) {
      // Pas de $_validation ==> pas d'edition de la fiche
      if (!$this->_old->fiche_ei_id) {
        // Nouvelle fiche
        $this->_obj->date_fiche = CMbDT::dateTime();
      }
      elseif ($this->_old->qualite_date_validation) {
        // NE RIEN FAIRE !! -> Attente de verif et de controle
      }
      elseif (!$this->_old->qualite_date_validation && $this->_old->service_date_validation) {
        $this->_obj->qualite_date_validation = CMbDT::dateTime();
      }
      elseif (!$this->_old->service_date_validation && $this->_old->date_validation) {
        $this->_obj->service_date_validation = CMbDT::dateTime();
      }
      elseif (!$this->_old->date_validation) {
        $this->_obj->date_validation = CMbDT::dateTime();
      }
    }
    $this->redirectStore = "m=$m&tab=vw_incidentvalid";
    parent::doStore();
  }
}

$do = new CDoFicheEiAddEdit;
$do->doIt();

