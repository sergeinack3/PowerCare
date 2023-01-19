<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CRHS;

/**
 * Class CHPrimXMLEvenementsServeurEtatsPatient
 * Serveur états patient
 */

class CHPrimXMLEvenementsServeurEtatsPatient extends CHPrimXMLEvenementsServeurActivitePmsi {
  public $actions = array(
    'création'     => "création",
    'remplacement' => "remplacement",
    'modification' => "modification",
    'suppression'  => "suppression",
    'information'  => "information",
  );

  /**
   * Construct
   *
   * @return CHPrimXMLEvenementsServeurEtatsPatient
   */
  function __construct() {
    $this->sous_type = "evenementServeurEtatsPatient";
    $this->evenement = "evt_serveuretatspatient";
    
    parent::__construct(null, "msgEvenementsServeurEtatsPatient");
  }

  /**
   * @inheritdoc
   */
  function generateEnteteMessage($type = null, $version = true , $group_id = null) {
    parent::generateEnteteMessage("evenementsServeurEtatsPatient");
  }

  /**
   * @inheritdoc
   */
  function generateFromOperation(CMbObject $object, $referent = false) {
    $evenementsServeurEtatsPatient = $this->documentElement;

    $receiver = $object->_receiver;

    switch ($object->_class) {
      case "CSejour":
        /** @var CSejour $sejour */
        $sejour = $object;

        // Ajout du patient
        $mbPatient = $sejour->loadRefPatient();
        if (!$mbPatient->_IPP) {
          $mbPatient->loadIPP($receiver->group_id);
        }
        $patient   = $this->addElement($evenementsServeurEtatsPatient, "patient");
        $this->addPatient($patient, $mbPatient, false, true);

        // Ajout de la venue, c'est-à-dire le séjour
        $venue = $this->addElement($evenementsServeurEtatsPatient, "venue");
        $this->addVenue($venue, $sejour, false, true);

        if (CAppUI::conf("hprimxml use_recueil")) {
          $recueil = $this->addElement($evenementsServeurEtatsPatient, "recueil");

          $identifiant = $this->addElement($recueil, "identifiant");
          $this->addElement($identifiant, "emetteur", "S-$sejour->_id");

          $this->addElement($recueil, "date", CMbDT::date($sejour->entree));
          $this->addElement($recueil, "heure", CMbDT::time($sejour->entree));

          $ufs       = $sejour->getUFs();
          $uf_heberg = CMbArray::get($ufs, "hebergement");
          $this->addCodeLibelle($recueil, "uniteFonctionnelle",
            $uf_heberg->_id ? $uf_heberg->code : "SNA", $uf_heberg->_id ? $uf_heberg->libelle : "Inconnu");
        }
        else {
          $dateObservation = $this->addElement($evenementsServeurEtatsPatient, "dateObservation");
          $this->addDateHeure($dateObservation, CMbDT::dateTime());
        }

        // Ajout des diagnostics
        $Diagnostics = $this->addElement($evenementsServeurEtatsPatient, "Diagnostics");
        $this->addDiagnosticsEtat($Diagnostics, $sejour);

        /*
        // Ajout de la naissance
        $naissance = new CNaissance();
        $naissance->sejour_maman_id = $sejour->_id;
        $naissance->loadMatchingObject();
        if ($naissance->_id) {
          $naissance = $this->addElement($evenementsServeurEtatsPatient, "naissance");
          $this->addNaissance($naissance, $sejour);
        }*/

        break;

      case "CRHS":
        /** @var CRHS $rhs */
        $rhs = $object;

        $sejour = $rhs->_ref_sejour;

        // Ajout du patient
        $mbPatient = $sejour->_ref_patient;
        $patient   = $this->addElement($evenementsServeurEtatsPatient, "patient");
        $this->addPatient($patient, $mbPatient, false, true);

        // Ajout de la venue, c'est-à-dire le séjour
        $venue = $this->addElement($evenementsServeurEtatsPatient, "venue");
        $this->addVenue($venue, $sejour, false, true);

        $date_cotation = null;
        // Date de début de la semaine de cotation
        if ($rhs->_in_bounds_mon) {
          $date_cotation = $rhs->date_monday;
        }
        elseif ($rhs->_in_bounds_tue) {
          $date_cotation = $rhs->_date_tuesday;
        }
        elseif ($rhs->_in_bounds_wed) {
          $date_cotation = $rhs->_date_wednesday;
        }
        elseif ($rhs->_in_bounds_thu) {
          $date_cotation = $rhs->_date_thursday;
        }
        elseif ($rhs->_in_bounds_fri) {
          $date_cotation = $rhs->_date_friday;
        }
        elseif ($rhs->_in_bounds_sat) {
          $date_cotation = $rhs->_date_saturday;
        }
        elseif ($rhs->_in_bounds_sun) {
          $date_cotation = $rhs->_date_sunday;
        }

        if (CAppUI::conf("hprimxml use_recueil")) {
          $recueil = $this->addElement($evenementsServeurEtatsPatient, "recueil");

          $identifiant = $this->addElement($recueil, "identifiant");
          $this->addElement($identifiant, "emetteur", "R-$rhs->_id");

          $this->addElement($recueil, "date", CMbDT::date($date_cotation));
          $this->addElement($recueil, "heure", CMbDT::time($date_cotation));

          $ufs       = $sejour->getUFs();
          $uf_heberg = CMbArray::get($ufs, "hebergement");
          $this->addCodeLibelle($recueil, "uniteFonctionnelle",
            $uf_heberg->_id ? $uf_heberg->code : "SNA", $uf_heberg->_id ? $uf_heberg->libelle : "Inconnu");
        }
        else {
          $dateObservation = $this->addElement($evenementsServeurEtatsPatient, "dateObservation");
          $this->addDateHeure($dateObservation, CMbDT::dateTimeXML($date_cotation));
        }
        // Ajout des diagnostics
        $Diagnostics = $this->addElement($evenementsServeurEtatsPatient, "Diagnostics");
        $this->addDiagnosticsEtatSSR($Diagnostics, $rhs);

        // Ajout des dépendances
        $dependances = $this->addElement($evenementsServeurEtatsPatient, "dependances");
        $action =  CAppUI::conf("hprimxml $this->evenement version") == "2.00" ? "creation" : "création";
        $this->addAttribute($dependances, "action", $action);
        $this->addDependances($dependances, $rhs);

        // Ajout des actes de réeducation
        $actesReeducation = $this->addElement($evenementsServeurEtatsPatient, "actesReeducation");
        $this->addActesReeducation($actesReeducation, $rhs);

        break;

      case "CExamIgs":
        /** @var CExamIgs $examIgs */
        $examIgs = $object;

        $sejour = $examIgs->_ref_sejour;

        // Ajout du patient
        $mbPatient = $sejour->_ref_patient;
        $patient   = $this->addElement($evenementsServeurEtatsPatient, "patient");
        $this->addPatient($patient, $mbPatient, false, true);

        // Ajout de la venue, c'est-à-dire le séjour
        $venue = $this->addElement($evenementsServeurEtatsPatient, "venue");
        $this->addVenue($venue, $sejour, false, true);

        $recueil = $this->addElement($evenementsServeurEtatsPatient, "recueil");

        $identifiant = $this->addElement($recueil, "identifiant");
        $this->addElement($identifiant, "emetteur", "E-$examIgs->_id");

        $this->addElement($recueil, "date" , CMbDT::date($examIgs->date));
        $this->addElement($recueil, "heure", CMbDT::time($examIgs->date));

        $ufs       = $sejour->getUFs();
        $uf_heberg = CMbArray::get($ufs, "hebergement");
        $this->addCodeLibelle($recueil, "uniteFonctionnelle",
          $uf_heberg->_id ? $uf_heberg->code : "SNA", $uf_heberg->_id ? $uf_heberg->libelle : "Inconnu");

        // Ajout de l'IGS
        $igs2 = $this->addElement($evenementsServeurEtatsPatient, "igs2");
        $this->addIGS2($igs2, $examIgs);

        break;

      default;
    }

    // Traitement final
    $this->purgeEmptyElements();
  }

  /**
   * Get contents XML
   *
   * @return array
   */
  public function getContentsXML(): array
  {
      $data  = [];
      $xpath = new CHPrimXPath($this);

      $evenementsServeurEtatsPatient = $xpath->queryUniqueNode("/hprim:evenementsServeurEtatsPatient");

      $data['patient']         = $xpath->queryUniqueNode("hprim:patient", $evenementsServeurEtatsPatient);
      $data['idSourcePatient'] = $this->getIdSource($data['patient']);
      $data['idCiblePatient']  = $this->getIdCible($data['patient']);

      $data['venue']           = $xpath->queryUniqueNode("hprim:venue", $evenementsServeurEtatsPatient);
      $data['idSourceVenue']   = $this->getIdSource($data['venue']);
    $data['idCibleVenue']    = $this->getIdCible($data['venue']);
    
    return $data; 
  }
}
