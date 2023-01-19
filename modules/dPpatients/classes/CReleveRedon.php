<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Relevés associés aux redons
 */
class CReleveRedon extends CMbObject {
  /** @var integer Primary key */
  public $releve_redon_id;

  // DB fields
  public $redon_id;
  public $date;
  public $user_id;
  public $qte_observee;
  public $vidange_apres_observation;
  public $constantes_medicales_id;

  // References
  /** @var CRedon */
  public $_ref_redon;

  /** @var CConstantesMedicales */
  public $_ref_constantes_medicales;

  // Form fields
  public $_qte_diff;
  public $_qte_cumul;
  public $_constante_medicale_id;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "releve_redon";
    $spec->key   = "releve_redon_id";
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["redon_id"]                  = "ref class|CRedon notNull back|releves";
    $props["date"]                      = "dateTime notNull";
    $props["user_id"]                   = "ref class|CMediusers back|releves_redons";
    $props["qte_observee"]              = "num min|0";
    $props["vidange_apres_observation"] = "bool";
    $props["constantes_medicales_id"]   = "ref class|CConstantesMedicales back|releve_redon";
    $props['_qte_diff']                 = "num";
    $props["_qte_cumul"]                = "num";
    return $props;
  }

  function store() {
    $this->completeField("qte_observee", "redon_id", "constantes_medicales_id", "date", "vidange_apres_observation");

    $redon = $this->loadRefRedon();

    $prev_releve = $redon->loadRefPrevReleve($this);
    $next_releve = $redon->loadRefNextReleve($this);

    if ($prev_releve->_id && !$prev_releve->vidange_apres_observation) {
        if ($this->qte_observee < $prev_releve->qte_observee) {
            return CAppUI::tr("CReleveRedon-Current quantity must be higher or equal than previous quantity");
        }
    }

    if ($next_releve->_id && !$next_releve->vidange_apres_observation) {
        if ($this->qte_observee > $next_releve->qte_observee) {
            return CAppUI::tr("CReleveRedon-Current quantity must be less or lower than last quantity");
        }
    }

    $creation = !$this->_id;

    if ($creation) {
      $this->user_id = CMediusers::get()->_id;
    }

    if (($this->date < $redon->date_pose) || ($redon->date_retrait && ($this->date > $redon->date_retrait))) {
      return CAppUI::tr("CReleveRedon-Out of borns");
    }

      if ($this->_qte_diff != null) {
          $releve_cste = $this->loadRefsConstantesMedicales();

          $releve_cste->datetime                     = $this->date;
          $releve_cste->context_class                = "CSejour";
          $releve_cste->context_id                   = $redon->sejour_id;
          $releve_cste->patient_id                   = $redon->loadRefSejour()->patient_id;
          $releve_cste->{$redon->constante_medicale} = $this->_qte_diff;

          if ($msg = $releve_cste->store()) {
              return $msg;
          }

          $this->constantes_medicales_id = $releve_cste->_id;
      }

    if ($msg = parent::store()) {
      return $msg;
    }

    return null;
  }

  /**
   * @inheritDoc
   */
    public function delete(): ?string
    {
        $this->completeField("constantes_medicales_id");

        $releve_cste   = $this->loadRefsConstantesMedicales();
        $releve_redons = $releve_cste->loadRefReleveRedons();

        // Remove quantity constante
        $this->deleteQuantityConstante($releve_cste);

        if ($msg = parent::delete()) {
            return $msg;
        }

        if ((count($releve_redons) == 1) && ($msg = $releve_cste->delete())) {
            return $msg;
        }

        return $msg;
    }

  /**
   * Charge le redon associé
   *
   * @return CRedon
   * @throws Exception
   */
  public function loadRefRedon() {
    return $this->_ref_redon = $this->loadFwdRef("redon_id", true);
  }

    /**
     * Delete quantity constante
     *
     * @param CConstantesMedicales $releve_cste
     *
     * @return string|null|void
     * @throws Exception
     */
    public function deleteQuantityConstante(CConstantesMedicales $releve_cste)
    {
        $redon          = $this->loadRefRedon();
        $constante_name = $redon->constante_medicale;
        $releves        = $redon->loadRefsReleves();

        if (count($releves) > 1) {
            $releve_cste->{$constante_name} -= $this->qte_observee;
        } else {
            $releve_cste->{$constante_name} = '';
        }

        if ($msg = $releve_cste->store()) {
            return $msg;
        }
    }

  /**
   * Charge le relevé de constante associé
   *
   * @return CConstantesMedicales
   * @throws Exception
   */
  public function loadRefsConstantesMedicales() {
    return $this->_ref_constantes_medicales = $this->loadFwdRef("constantes_medicales_id", true);
  }

  public function getQteCumul(): float
  {
    $redon = $this->loadRefRedon();

    /** @var static[] $releves */
    $releves = array_reverse($redon->loadRefsReleves());

    $cumul = 0.0;

    $prev_qte = 0.0;
    $prev_vidange = false;

    foreach ($releves as $_releve) {
        if ($prev_vidange || !$cumul) {
            $cumul += $_releve->qte_observee;
        } else {
            $cumul += ($_releve->qte_observee - $prev_qte);
        }

        $prev_qte = $_releve->qte_observee;
        $prev_vidange = $_releve->vidange_apres_observation;

        if ($this->_id === $_releve->_id) {
            break;
        }
    }

    return $this->_qte_cumul = $cumul;
  }
}
