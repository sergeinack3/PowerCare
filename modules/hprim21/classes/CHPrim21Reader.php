<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CMbDT;

/**
 * The HPRIM 2.1 reader class
 */
class CHPrim21Reader implements IShortNameAutoloadable {
  
  public $has_header = false;
  
  // Champs header
  public $separateur_champ;
  public $separateur_sous_champ;
  public $repetiteur;
  public $echappement;
  public $separateur_sous_sous_champ;
  public $nom_fichier;
  public $mot_de_passe;
  public $id_emetteur;
  public $sous_type;
  public $tel_emetteur;
  public $carac_trans;
  public $id_recepteur;
  public $commentaire;
  public $mode_traitement;
  public $version;
  public $type;
  public $date;
  
  // Nombre d'éléments
  public $nb_patients;
  
  // Log d'erreur
  public $error_log = array();

  /** @var  CEchangeHprim21 */
  public $_echange_hprim21;
  
  function bindEchange($fileName = null) {
    $this->_echange_hprim21->date_production   = CMbDT::dateTime($this->date);
    $this->_echange_hprim21->version           = $this->version;
    $this->_echange_hprim21->nom_fichier       = $this->nom_fichier;
    // Read => Mediboard
    $this->_echange_hprim21->receiver_id       = null;
    $this->_echange_hprim21->sous_type         = $this->sous_type;
    $this->_echange_hprim21->type              = $this->type;
    $this->_echange_hprim21->send_datetime     = CMbDT::dateTime();
    if ($fileName) {
      $this->_echange_hprim21->_message        = file_get_contents($fileName);
    }
    return $this->_echange_hprim21;
  }
  
  function readFile($fileName = null, $file = null) {
    if ($fileName) {
      $file = fopen($fileName, 'rw' );
    }
    
    if (!$file) {
      $this->error_log[] = "Fichier non trouvé";
      return null;
    }
    
    $i = 0;
    $lines = array();
    while (!feof($file)) {
      if (!$i) {
        $header = trim(fgets($file, 1024));
        $i++;
      }
      else {
        $_line = trim(fgets($file, 1024));
        if ($_line) {
          // On vérifie si la ligne est un Addendum
          if (substr($_line, 0, 2) == "A|") {
            $lines[$i-1] .= substr($_line, 2);
          }
          else {
            $lines[$i] = $_line;
            $i++;
          }
        }
      }
    }

    fclose($file);
    
    // Lecture de l'en-tête
    if (!$this->segmentH($header)) {
      return null;
    }    
    
    // Lecture du message
    switch ($this->sous_type) {
      // De demandeur (d'analyses ou d'actes de radiologie) à exécutant
      case "ADM" :
        // Transfert de données d'admission
        return $this->messageADM($lines);
        break;
      case "ORM" :
        // transfert de demandes d'analyses = prescription
        return $this->messageORM($lines);
        break;
      case "REG" :
        // transfert de données de règlement
        return $this->messageREG($lines);
        break;
      //D'exécutant à demandeur 
      case "ORU" :
        // transfert de résultats d'analyses
        return $this->messageORU($lines);
        break;
      case "FAC" :
        // transfert de données de facturation
        return $this->messageFAC($lines);
        break;
      // Bidirectionnel
      case "ERR" :
        // transfert de messages d'erreur
        return $this->messageERR($lines);
        break;
      default :
        $this->error_log[] = "Type de message non reconnu";
        return false;
    }
  }
  
  // Fonction de prise en charge des messages
  function messageADM($lines) {
    $nbLine = count($lines);
    $i = 1;
    while ($i <= $nbLine && $this->getTypeLine($lines[$i]) == "P") {
      $patient = new CHprim21Patient();
      if (!$this->segmentP($lines[$i], $patient)) {
        return false;
      }
      $i++;
      if ($i < $nbLine && $this->getTypeLine($lines[$i]) == "AP") {
        if (!$this->segmentAP($lines[$i], $patient)) {
          return false;
        }
        $i++;
        while ($i < $nbLine && $this->getTypeLine($lines[$i]) == "AC") {
          $complementaire = new CHprim21Complementaire();
          if (!$this->segmentAC($lines[$i], $complementaire, $patient)) {
            return false;
          }
          $i++;
        }
      }
    }
    if (!isset($lines[$i]) || $this->getTypeLine($lines[$i]) != "L") {
      $this->error_log[] = "Erreur dans la suite des segments du message ADM";
      return false;
    }
    return $this->segmentL($lines[$i]);
  }
  
  function messageORM($lines) {
    $this->error_log[] = "Message ORM non pris en charge";
    return false;
  }
  
  function messageREG($lines) {
    $this->error_log[] = "Message REG non pris en charge";
    return false;
  }
  
  function messageORU($lines) {
    $this->error_log[] = "Message ORU non pris en charge";
    return false;
  }
  
  function messageFAC($lines) {
    $this->error_log[] = "Message FAC non pris en charge";
    return false;
  }
  
  function messageERR($lines) {
    $this->error_log[] = "Message ERR non pris en charge";
    return false;
  }

  /**
   * Fonctions de prise en charge des segments
   *
   * @param string $line Ligne du fichier analysé
   *
   * @return string
   */
  function getTypeLine($line) {
    $lines = explode($this->separateur_champ, $line);
    $type = reset($lines);
    return $type;
  }
  
  function segmentH($line) {
    if (strlen($line) < 6) {
      $this->error_log[] = "Segment header trop court";
      return false;
    }
    $this->separateur_champ           = $line[1];
    $this->separateur_sous_champ      = $line[2];
    $this->repetiteur                 = $line[3];
    $this->echappement                = $line[4];
    $this->separateur_sous_sous_champ = $line[5];
    $line = substr($line, 7);
    $champs = explode($this->separateur_champ, $line);
    if (count($champs) < 12) {
      $this->error_log[] = "Champs manquant dans le segment header";
      return false;
    }
    $this->nom_fichier       = $champs[0];
    $this->mot_de_passe      = $champs[1];
    $emetteur                = explode($this->separateur_sous_champ, $champs[2]);
    $this->id_emetteur       = $emetteur[0];
    $this->sous_type         = $champs[4];
    $this->carac_trans       = $champs[6];
    $recepteur               = explode($this->separateur_sous_champ, $champs[7]);
    $this->commentaire       = $champs[8];
    $this->mode_traitement   = $champs[9];
    $version_type            = explode($this->separateur_sous_champ, $champs[10]);
    $this->version           = $version_type[0];
    $this->type              = $version_type[1];
    $this->date              = $champs[11];
    $this->has_header        = true;
    
    return true;
  }

  /**
   * @param string          $line     Ligne analysée
   * @param CHprim21Patient &$patient Patient lié
   *
   * @return bool
   */
  function segmentP($line, &$patient) {
    if (!$this->has_header) {
      return false;
    }
   
    if (!$patient->bindToLine($line, $this)) {
      return false;
    }
    $patient->store();
    $medecin = new CHprim21Medecin();
    if ($medecin->bindToLine($line, $this)) {
      if ($medecin->external_id) {
        $medecin->store();
      }
    }
    $sejour = new CHprim21Sejour();
    if ($sejour->bindToLine($line, $this, $patient, $medecin)) {
      if ($sejour->external_id) {
        $sejour->store();
      }
    }
    return true;
  }
  
  function segmentOBR($line) {
    CApp::log("Demande d'analyses ou d'actes", $line);
    if (!$this->has_header) {
      return false;
    }
    return true;
  }
  
  function segmentOBX($line) {
    CApp::log("Résultat d'un test", $line);
    if (!$this->has_header) {
      return false;
    }
    return true;
  }
  
  function segmentC($line) {
    CApp::log("Commentaire", $line);
    if (!$this->has_header) {
      return false;
    }
    return true;
  }
  
  function segmentL($line) {
    if (!$this->has_header) {
      return false;
    }
    return true;
  }
  
  function segmentA($line) {
    CApp::log("Addendum", $line);
    if (!$this->has_header) {
      return false;
    }
    return true;
  }
  
  function segmentFAC($line) {
    CApp::log("En-tête de facture", $line);
    if (!$this->has_header) {
      return false;
    }
    return true;
  }
  
  function segmentACT($line) {
    CApp::log("Ligne de facture", $line);
    if (!$this->has_header) {
      return false;
    }
    return true;
  }
  
  function segmentREG($line) {
    CApp::log("Elément de règlement", $line);
    if (!$this->has_header) {
      return false;
    }
    return true;
  }

  /**
   * @param string          $line     Ligne analysée
   * @param CHprim21Patient &$patient Patient lié
   *
   * @return bool
   */
  function segmentAP($line, &$patient) {
    if (!$this->has_header) {
      return false;
    }
    $patient->bindAssurePrimaireToLine($line, $this);
    $patient->store();
    return true;
  }

  /**
   * @param string                 $line             Ligne analysée
   * @param CHprim21Complementaire &$complementaire  Complémentaire liée
   * @param CHprim21Patient        $patient          Patient lié
   *
   * @return bool
   */
  function segmentAC($line, &$complementaire, $patient) {
    if (!$this->has_header) {
      return false;
    }
    $complementaire->bindToLine($line, $this, $patient);
    $complementaire->store();
    return true;
  }
  
  function segmentERR($line) {
    CApp::log("Message d'erreur", $line);
    if (!$this->has_header) {
      return false;
    }
    return true;
  }
}
