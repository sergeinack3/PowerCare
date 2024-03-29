<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use DOMNode;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHPrimSanteRecordPayment
 * Record payment, message XML
 */
class CHPrimSanteRecordPayment extends CHPrimSanteMessageXML {
  /**
   * @see parent::getContentNodes
   */
  function getContentNodes() {
    $data = array();

    $exchange_hpr = $this->_ref_exchange_hpr;
    $sender       = $exchange_hpr->_ref_sender;
    $sender->loadConfigValues();

    //$reg_patient = $this->queryNode("REG.PATIENT", null, $varnull, true);
    $this->queryNodes("//REG", null, $data, true); // get ALL the REG segments

    return $data;
  }

  /**
   * @inheritdoc
   */
  function handle(CHPrimSanteAcknowledgment $ack, CMbObject $object, $data) {
    // Traitement du message des erreurs

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

    // R�cup�ration des r�glements
    foreach ($regs as $_REG) {
      $sejour = new CSejour();

      $NDA         = $this->getNDA($_REG);
      $user_reg    = $this->getUser($_REG);
      $segment_row = $this->getREGSegmentRow($_REG);

      // Recherche si on retrouve le s�jour
      if (!$this->admitFound($NDA, $sejour)) {
        $errors[] = new CHPrimSanteError($exchange_hpr, "P", "06", array("REG", $segment_row, array($NDA, $user_reg)), "22.3");
        continue;
      }

      $consults      = array();
      $consultations = $sejour->loadRefsConsultations();

      // S�lection des consultations �ligibles
      foreach ($consultations as $_consult) {
        $user = $_consult->loadRefPraticien();

        if ($user_reg) {
          if ($user->adeli == $user_reg) {
            $consults[$_consult->_id] = $_consult;
          }

          continue;
        }
      }

      // Si une seule consultation donn�e
      if (!count($consults) && count($consultations) == 1) {
        $consults = $consultations;
      }

      $consultation  = new CConsultation();
      // On essaie d'en trouver une qui ne soit pas acquitt�e
      foreach ($consults as $_consult) {
        $facture = $_consult->loadRefFacture();

        if (!$facture->patient_date_reglement) {
          $consultation = $_consult;
          break;
        }
      }

      // Aucune consultation trouv�e
      if (!$consultation->_id && count($consults) > 0) {
        $consultation = end($consults);
      }

      if (!$consultation || !$consultation->_id) {
        $errors[] = new CHPrimSanteError($exchange_hpr, "P", "01", array("REG", $segment_row, array($NDA, $user_reg)), "22.3");
        continue;
      }

      $facture = $consultation->loadRefFacture();
      if (!$facture->_id) {
        CFacture::save($consultation);
        $facture = $consultation->loadRefFacture();
        if (!$facture->_id) {
          $errors[] = new CHPrimSanteError($exchange_hpr, "P", "02", array("REG", $segment_row, array($NDA, $user_reg)), "22.3");
          continue;
        }
      }

      // Recherche d'un regl�ment par tag + idex (nom fichier - id reg)
      $idex_value = $NDA."_".$segment_row;
      $tag        = CHPrimSante::getTag($sender->group_id);

      $idex = CIdSante400::getMatch("CReglement", $tag, $idex_value);

      // Mapping des r�glements
      $return_payment = $this->mapAndStorePayment($_REG, $facture, $idex);
      if (is_string($return_payment)) {
        $errors[] = new CHPrimSanteError($exchange_hpr, "P", "03", array("REG", $segment_row, array($NDA, $user_reg)), "22.3", $return_payment);
        continue;
      }
    }

    return $exchange_hpr->setAck($ack, $errors, $object);
  }

  /**
   * found admit
   *
   * @param String  $NDA    nda
   * @param CSejour $sejour sejour
   *
   * @return bool
   */
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

  /**
   * get the reg segment row
   *
   * @param DOMNode $node node
   *
   * @return string
   */
  function getREGSegmentRow(DOMNode $node) {
    return $this->queryTextNode("REG.1", $node);
  }

  /**
   * get the nda
   *
   * @param DOMNode $node node
   *
   * @return string
   */
  function getNDA(DOMNode $node) {
    return $this->queryTextNode("REG.2", $node);
  }

  /**
   * get the user
   *
   * @param DOMNode $node node
   *
   * @return string
   */
  function getUser(DOMNode $node) {
    return $this->queryTextNode("REG.7", $node);
  }

  /**
   * get the amount paid
   *
   * @param DOMNode $node node
   *
   * @return string
   */
  function getAmountPaid(DOMNode $node) {
    return $this->queryTextNode("REG.3/AM.1", $node);
  }

  /**
   * get the direction
   *
   * @param DOMNode $node node
   *
   * @return string
   */
  function getDirection(DOMNode $node) {
    return $this->queryTextNode("REG.4", $node);
  }

  /**
   * get the date payment
   *
   * @param DOMNode $node node
   *
   * @return string
   */
  function getDatePayment(DOMNode $node) {
    return CMbDT::date($this->queryTextNode("REG.5/TS.1", $node));
  }

  /**
   * map and store the payment
   *
   * @param DOMNode         $node    node
   * @param CFactureCabinet $facture facture
   * @param CIdSante400     $idex    idex
   *
   * @return string
   */
  function mapAndStorePayment(DOMNode $node, CFactureCabinet $facture, CIdSante400 $idex) {
    $reglement = new CReglement();
    $reglement->load($idex->object_id);

    // Recherche du r�glement si pas retrouv� par son idex
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

    // Mise � jour du montant (du_tiers) de la facture
    $value = ($reglement->_old) ? ($reglement->montant - $reglement->_old->montant) : $reglement->montant;

    // Acquittement de la facture associ�e ?
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