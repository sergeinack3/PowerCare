<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use DOMNode;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHprim21RecordPayment 
 * Record payment, message XML
 */
class CHprim21RecordPayment extends CHPrim21MessageXML {  
  function getContentNodes() {
    $data = array();
    
    $exchange_hpr = $this->_ref_exchange_hpr;
    $sender       = $exchange_hpr->_ref_sender;
    $sender->loadConfigValues();    
    
    //$reg_patient = $this->queryNode("REG.PATIENT", null, $varnull, true);
    $this->queryNodes("//REG", null, $data, true); // get ALL the REG segments

    return $data;
  }
 
  function handle($ack, CMbObject $object, $data) {
    // Traitement du message des erreurs
    $comment = "";

    $exchange_hpr = $this->_ref_exchange_hpr;
    $exchange_hpr->_ref_sender->loadConfigValues();
    $sender       = $exchange_hpr->_ref_sender;
    
    $this->_ref_sender = $sender;
    
    CMbObject::$useObjectCache = false;
    
    // Rejets partiels du message
    $errors = array();

    if (isset($data["REG"])) {
      $regs = $data["REG"];
    }
    else {
      $regs = $data["//REG"];
    }
    
    // Récupération des règlements
    foreach ($regs as $_REG) {
      $sejour = new CSejour();
      
      $NDA         = $this->getNDA($_REG);
      $user_reg    = $this->getUser($_REG);
      $segment_row = $this->getREGSegmentRow($_REG);
      
      // Recherche si on retrouve le séjour
      if (!$this->admitFound($NDA, $sejour)) {
        $errors[] = $this->addError(
          "P",
          null,
          array(
            "REG",
            $segment_row,
            array(
              $NDA,
              $user_reg
            )
          ),
          null,
          $NDA,
          "I",
          CAppUI::tr("CHL7EventADT-P-01", $NDA)
        );
        continue;
      }
      
      $consults      = array();
      $consultations = $sejour->loadRefsConsultations();
            
      // Sélection des consultations éligibles
      foreach ($consultations as $_consult) {
        $user = $_consult->loadRefPraticien();
        
        if ($user_reg) {
          if ($user->adeli == $user_reg) {
            $consults[$_consult->_id] = $_consult;
          }
          
          continue;
        }
      }
      
      // Si une seule consultation donnée
      if (!count($consults) && count($consultations) == 1) {
        $consults = $consultations;
      } 
      
      $consultation  = new CConsultation();
      // On essaie d'en trouver une qui ne soit pas acquittée
      foreach ($consults as $_consult) {
        $facture = $_consult->loadRefFacture();
        
        if (!$facture->patient_date_reglement) {
          $consultation = $_consult;
          break;
        }
      }

      // Aucune consultation trouvée
      if (!$consultation->_id && count($consults) > 0) {
        $consultation = end($consults);
      }
      
      if (!$consultation || !$consultation->_id) {
        $errors[] = $this->addError(
          "P",
          null,
          array(
            "REG",
            $segment_row,
            array(
              $NDA,
              $user_reg
            )
          ),
          null,
          $NDA,
          "I",
          CAppUI::tr("CHL7EventADT-P-02")
        );
        continue;
      }
      
      $facture = $consultation->loadRefFacture();
      if (!$facture->_id) {
        CFacture::save($consultation);
        $facture = $consultation->loadRefFacture();
        if (!$facture->_id) {
          $errors[] = $this->addError(
            "P",
            null,
            array(
              "REG",
              $segment_row,
              array(
                $NDA,
                $user_reg
              )
            ),
            null,
            $NDA,
            "I",
            CAppUI::tr("CHL7EventADT-P-03")
          );
          continue;
        }
      }
      
      // Recherche d'un reglèment par tag + idex (nom fichier - id reg)
      $idex_value = $NDA."_".$segment_row;
      $tag        = CHprim21::getTag($sender->group_id);

      $idex = CIdSante400::getMatch("CReglement", $tag, $idex_value);
      
      // Mapping des règlements
      $return_payment = $this->mapAndStorePayment($_REG, $facture, $idex);
      if (is_string($return_payment)) {
        $errors[] = $this->addError(
          "P",
          null,
          array(
            "REG",
            $segment_row,
            array(
              $NDA,
              $user_reg
            )
          ),
          null,
          $NDA,
          "I",
          CAppUI::tr("CHL7EventADT-P-04", $return_payment)
        );
        continue;
      }      
    }

    if (count($errors) > 0) {
      return $exchange_hpr->setAckP($ack, $errors, $object);
    }
    
    return $exchange_hpr->setAckI($ack, null, $object);
  } 

  function addError($gravite, $ligne, $adr_segment, $donnee, $valeur, $type, $error_code) {
    return array(
      $gravite,
      $ligne,
      $adr_segment,
      $donnee,
      $valeur,
      $type,
      $error_code
    );
  }
  
  function admitFound($NDA, CSejour $sejour) {
    $sender = $this->_ref_sender;

    // NDA    
    $idexNDA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $NDA);
    
    if ($idexNDA->_id) {
      $sejour->load($idexNDA->object_id);
      
      return true;
    }
    
    return false;
  }
  
  function getREGSegmentRow(DOMNode $node) {
    return $this->queryTextNode("REG.1", $node);  
  }
  
  function getNDA(DOMNode $node) {
    return $this->queryTextNode("REG.2", $node);  
  }
  
  function getUser(DOMNode $node) {
    return $this->queryTextNode("REG.7", $node);
  } 
  
  function getAmountPaid(DOMNode $node) {
    return $this->queryTextNode("REG.3/AM.1", $node);
  }
  
  function getDirection(DOMNode $node) {
    return $this->queryTextNode("REG.4", $node);
  }
  
  function getDatePayment(DOMNode $node) {
    return CMbDT::date($this->queryTextNode("REG.5/TS.1", $node));
  }
  
  function mapAndStorePayment(DOMNode $node, CFactureCabinet $facture, CIdSante400 $idex) {
    $reglement = new CReglement();
    $reglement->load($idex->object_id);     
    
    // Recherche du règlement si pas retrouvé par son idex
    $reglement->setObject($facture);    
    $reglement->date     = $this->getDatePayment($node)." 00:00:00";
    
    $amount_paid = $this->getAmountPaid($node);
    $reglement->montant  = $amount_paid;
    
    $direction = $this->getDirection($node);
    if ($direction == "-") {
      $reglement->montant = $reglement->montant * -1;
    }
    $reglement->emetteur = "tiers";
    $reglement->mode     = "autre";
    
    $reglement->loadOldObject();
    
    if ($reglement->_old && round($reglement->montant, 3) == round($reglement->_old->montant, 3)) {
      return $reglement;
    }
         
    // Mise à jour du montant (du_tiers) de la facture         
    $value = ($reglement->_old) ? ($reglement->montant - $reglement->_old->montant) : $reglement->montant; 
    
    // Acquittement de la facture associée ?
    if ($msg = $reglement->store()) {
      return $msg;
    }

    // Gestion de l'idex
    if (!$idex->object_id) {
      $idex->object_id = $reglement->_id;
    }
    if ($msg = $idex->store()) {
      return $msg;
    }

    if ($direction != "+") {
      return $reglement;
    }

    return $reglement;
  } 
}