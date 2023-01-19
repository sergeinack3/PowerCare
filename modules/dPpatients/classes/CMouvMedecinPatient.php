<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;


use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Sante400\CMouvement400;

/**
 * Handle patient syncing with medecin table
 */
class CMouvMedecinPatient extends CMouvement400 {

  /**
   * Object constructor
   */
  function __construct() {
    parent::__construct();
    $this->base   = CAppUI::conf("sante400 dsn");
    $this->table  = "medecin_trigger";
    $this->origin = "medecin";

    $this->type_field        = "type";
    $this->when_field        = "datetime";
    $this->trigger_key_field = "trigger_id";
    $this->origin_key_field  = "medecin_id";

    $this->old_prefix    = "old_";
    $this->new_prefix    = "new_";
    $this->origin_prefix = "";
  }

  /**
   * @see parent::initialize()
   */
  function initialize() {
    parent::initialize();

    $this->value_prefix = $this->type == "delete" ? $this->old_prefix : $this->new_prefix;
  }

  /**
   * @see parent::synchronize()
   */
  function synchronize() {
    $this->syncPatient();

    $this->starStatus(self::STATUS_ETABLISSEMENT);
    $this->starStatus(self::STATUS_FONCSALLSERV);
    $this->starStatus(self::STATUS_PRATICIEN);
    $this->starStatus(self::STATUS_SEJOUR);
    $this->starStatus(self::STATUS_OPERATION);
    $this->starStatus(self::STATUS_PRATICIEN);
    $this->starStatus(self::STATUS_ACTES);
    $this->starStatus(self::STATUS_NAISSANCE);
  }

  function syncPatient($update = true) {
    $medecin_id = $this->consume("medecin_id");

    // Gestion des id400
    $tag                = "medecin-patient";
    $idex               = new CIdSante400();
    $idex->object_class = "CPatient";
    $idex->id400        = $medecin_id;
    $idex->tag          = $tag;

    // Identité
    $patient         = new CPatient;
    $patient->nom    = $this->consume("nom");
    $patient->prenom = CValue::first($this->consume("prenom"), $patient->nom);

    // Simulation de l'âge
    $year               = 1980 - strlen($patient->nom);
    $month              = '01';
    $day                = str_pad(strlen($patient->prenom) % 30, 2, '0', STR_PAD_LEFT);
    $patient->naissance = "$year-$month-$day";

    // Binding
    $this->trace($patient->getProperties(true), "Patient à enregistrer");
    $idex->bindObject($patient);

    $this->markStatus(self::STATUS_PATIENT);
  }

}

