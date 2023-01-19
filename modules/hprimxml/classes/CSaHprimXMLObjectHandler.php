<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Ox\Mediboard\Ssr\CRHS;

/**
 * SA H'XML object handler
 */
class CSaHprimXMLObjectHandler extends CHprimXMLObjectHandler {
  /**
   * @var array
   */
  static $handled = array ("CSejour", "COperation", "CConsultation", "CRHS", "CActeNGAP", "CActeCCAM", "CExamIgs", "CDossierMedical");

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    return in_array($object->_class, self::$handled);
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    /** @var CInteropReceiver $receiver */
    $receiver = $object->_receiver;

    $codable = $object;
    switch ($object->_class) {
      // CSejour 
      // Envoi des actes / diags soit quand le séjour est facturé, soit quand le sejour a une sortie réelle,
      // soit quand on a la clôture sur le sejour
      case 'CSejour':
        /** @var CSejour $sejour */
        $sejour = $object;
        $sejour->_NDA = null;
        $sejour->loadNDA($receiver->group_id);

        // Si le group_id du séjour est différent de celui du destinataire
        if ($sejour->group_id != $receiver->group_id) {
          return false;
        }
        
        $patient = $sejour->loadRefPatient();
        $patient->_IPP = null;
        $patient->loadIPP($receiver->group_id);

        // Destinataire gère seulement les non facturables
        if ($receiver->_configs["send_no_facturable"] == "0" && $sejour->facturable) {
          return false;
        }

        // Destinataire gère seulement les facturables
        if ($receiver->_configs["send_no_facturable"] == "2" && !$sejour->facturable) {
          return false;
        }

        if (CAppUI::conf("sa CSa send_only_with_ipp_nda", $sejour->loadRefEtablissement()->_guid)) {
          if (!$patient->_IPP || !$sejour->_NDA) {
            throw new CMbException("CSaObjectHandler-send_only_with_ipp_nda", UI_MSG_ERROR);
          }
        }

        // Envoi des diags au fil de l'eau
        if (CAppUI::conf("sa CSa send_diag_immediately", $sejour->loadRefEtablissement())) {
          $sejour->loadOldObject();
          if (($sejour->fieldModified("DP") || $sejour->fieldModified("DR")) ||
            (($sejour->_old->DP && !$sejour->DP) || ($sejour->_old->DR && !$sejour->DR))) {
            $sejour->_receiver = $receiver;

            $evt = (CAppUI::conf("hprimxml send_diagnostic") === "evt_serveuretatspatient") ?
              "CHPrimXMLEvenementsServeurEtatsPatient" : "CHPrimXMLEvenementsPmsi";

            $this->sendEvenementPMSI($evt, $sejour);
          }
        }
      
        break;
      // COperation
      // Envoi des actes soit quand l'interv est facturée, soit quand on a la clôture sur l'interv
      case 'COperation':
        /** @var COperation $operation */
        $operation = $object;

        $sejour  = $operation->loadRefSejour();
        $sejour->_NDA = null;
        $sejour->loadNDA($receiver->group_id);

        // Si le group_id du séjour est différent de celui du destinataire
        if ($sejour->group_id != $receiver->group_id) {
          return false;
        }

        // Destinataire gère seulement les non facturables
        if ($receiver->_configs["send_no_facturable"] == "0" && $sejour->facturable) {
          return false;
        }

        // Destinataire gère seulement les facturables
        if ($receiver->_configs["send_no_facturable"] == "2" && !$sejour->facturable) {
          return false;
        }
        
        $patient = $sejour->loadRefPatient();
        $patient->_IPP = null;
        $patient->loadIPP($receiver->group_id);
        
        break;
      // CConsultation
      // Envoi des actes dans le cas de la clôture de la cotation
      case 'CConsultation':
        /** @var CConsultation $consultation */
        $consultation = $object;

        $patient = $consultation->loadRefPatient();
        $patient->_IPP = null;
        $patient->loadIPP($receiver->group_id);
        
        $sejour = $consultation->loadRefSejour();
        $sejour->_NDA = null;
        $sejour->loadNDA($receiver->group_id);

        // Si le group_id du séjour est différent de celui du destinataire
        if ($sejour->group_id != $receiver->group_id) {
          return false;
        }

        // Destinataire gère seulement les non facturables
        if ($receiver->_configs["send_no_facturable"] == "0" && $sejour->facturable) {
          return false;
        }

        // Destinataire gère seulement les facturables
        if ($receiver->_configs["send_no_facturable"] == "2" && !$sejour->facturable) {
          return false;
        }
        
        break;

      // CRHS
      // Envoi des actes CSARR
      case 'CRHS':
        /** @var CRHS $rhs */
        $rhs = $object;

        $sejour  = $rhs->loadRefSejour();
        $sejour->_NDA = null;
        $sejour->loadNDA($receiver->group_id);

        // Si le group_id du séjour est différent de celui du destinataire
        if ($sejour->group_id != $receiver->group_id) {
          return false;
        }

        // Destinataire gère seulement les non facturables
        if ($receiver->_configs["send_no_facturable"] == "0" && $sejour->facturable) {
          return false;
        }

        // Destinataire gère seulement les facturables
        if ($receiver->_configs["send_no_facturable"] == "2" && !$sejour->facturable) {
          return false;
        }

        $patient = $sejour->loadRefPatient();
        $patient->_IPP = null;
        $patient->loadIPP($receiver->group_id);

        $this->sendEvenementPMSI("CHPrimXMLEvenementsServeurEtatsPatient", $rhs);

        return false;

      // CActeNGAP | CActeCCAM
      // On envoie les actes au fil de l'eau (pas un envoi récapitulatif)
      case "CActeNGAP":
      case "CActeCCAM":
        if (!CAppUI::conf("sa CSa send_acte_immediately", $receiver->_ref_group->_guid)) {
          return false;
        }

        /** @var CActeCCAM|CActeNGAP $acte */
        $acte = $object;

        /** @var CCodable $codable */
        $codable = $acte->loadTargetObject();
        $codable->_receiver = $receiver;
        $sejour  = (!$codable instanceof CSejour) ? $codable->loadRefSejour(): $codable;

        // Cas ou le codable est un CDevisCodage ou un CModelCodage
        if (!$sejour || !$sejour instanceof CSejour) {
          return false;
        }

        // Si le group_id du séjour est différent de celui du destinataire
        if ($sejour->group_id != $receiver->group_id) {
          return false;
        }

        // Destinataire gère seulement les non facturables
        if ($receiver->_configs["send_no_facturable"] == "0" && $sejour->facturable) {
          return false;
        }

        // Destinataire gère seulement les facturables
        if ($receiver->_configs["send_no_facturable"] == "2" && !$sejour->facturable) {
          return false;
        }

        $sejour->_NDA = null;
        $sejour->loadNDA($receiver->group_id);
        $patient = $codable->loadRefPatient();
        $patient->_IPP = null;
        $patient->loadIPP($receiver->group_id);

        break;

      case "CExamIgs":
        if (!CAppUI::conf("sa CSa send_igs_immediately", $receiver->_ref_group->_guid)) {
          return false;
        }

        /** @var CExamIgs $exam_igs */
        $exam_igs = $object;
        $sejour = $exam_igs->loadRefSejour();

        // Si le group_id du séjour est différent de celui du destinataire
        if ($sejour->group_id != $receiver->group_id) {
          return false;
        }

        // Destinataire gère seulement les non facturables
        if ($receiver->_configs["send_no_facturable"] == "0" && $sejour->facturable) {
          return false;
        }

        // Destinataire gère seulement les facturables
        if ($receiver->_configs["send_no_facturable"] == "2" && !$sejour->facturable) {
          return false;
        }

        $sejour->_NDA = null;
        $sejour->loadNDA($receiver->group_id);
        $patient = $sejour->loadRefPatient();
        $patient->_IPP = null;
        $patient->loadIPP($receiver->group_id);

        $this->sendEvenementPMSI("CHPrimXMLEvenementsServeurEtatsPatient", $exam_igs);

        return false;

      case "CDossierMedical";
        /** @var CDossierMedical $dossier_medical */
        $dossier_medical = $object;

        /** @var CSejour $sejour */
        $sejour = $dossier_medical->_ref_object;

        if (!$sejour) {
          return false;
        }

        // Si le group_id du séjour est différent de celui du destinataire
        if ($sejour->group_id != $receiver->group_id) {
          return false;
        }

        // Destinataire gère seulement les non facturables
        if ($receiver->_configs["send_no_facturable"] == "0" && $sejour->facturable) {
          return false;
        }

        // Destinataire gère seulement les facturables
        if ($receiver->_configs["send_no_facturable"] == "2" && !$sejour->facturable) {
          return false;
        }

        $sejour->_NDA = null;
        $sejour->loadNDA($receiver->group_id);
        $patient = $sejour->loadRefPatient();
        $patient->_IPP = null;
        $patient->loadIPP($receiver->group_id);

        $sejour->_receiver = $receiver;
        $sejour->_ref_dossier_medical = $dossier_medical;
        $sejour->loadOldObject();

        $this->sendEvenementPMSI("CHPrimXMLEvenementsServeurEtatsPatient", $sejour);
        return false;

      default:
    }

    /** @var CPatient $patient */
    /** @var CSejour  $sejour */
    if (CAppUI::conf("sa CSa send_only_with_ipp_nda", $sejour->loadRefEtablissement()->_guid)) {
      if (!$patient->_IPP || !$sejour->_NDA) {
        throw new CMbException("CSaObjectHandler-send_only_with_ipp_nda", UI_MSG_ERROR);
      }
    }

    if (!CAppUI::conf("sa CSa send_diag_immediately", $sejour->loadRefEtablissement()->_guid)) {
      if (CAppUI::conf("sa CSa send_diags_with_actes", $sejour->loadRefEtablissement()->_guid)) {
        if ($sejour->DP || $sejour->DR || (count($sejour->loadRefDossierMedical()->_codes_cim) > 0)) {
          $sejour->_receiver = $receiver;

          $evt = (CAppUI::conf("hprimxml send_diagnostic") === "evt_serveuretatspatient") ?
            "CHPrimXMLEvenementsServeurEtatsPatient" : "CHPrimXMLEvenementsPmsi";

          $this->sendEvenementPMSI($evt, $sejour);
        }
      }
    }

    if (CAppUI::conf("sa CSa send_acte_immediately", $receiver->_ref_group->_guid)
        && (!$object instanceof CActeCCAM && !$object instanceof CActeNGAP)
    ) {
      return false;
    }

    $codable->_ref_actes_ccam = array();
    $codable->_ref_actes_ngap = array();

    // On value les champs uniquement si on ajoute/modifie/supprime un acte CCAM/NGAP
    if ($object instanceof CActeNGAP) {
      $codable->_ref_actes_ngap = array($object);
    }
    elseif ($object instanceof CActeCCAM) {
      $codable->_ref_actes_ccam = array($object);
    }
    else {
      // Chargement des actes du codable
      $codable->loadRefsActes();
    }

    // Envoi des actes CCAM / NGAP
    if (empty($codable->_ref_actes_ccam) && empty($codable->_ref_actes_ngap)) {
      return false;
    }

    // Flag les actes CCAM en envoyés
    foreach ($codable->_ref_actes_ccam as $_acte_ccam) {
      $_acte_ccam->sent = 1;
      $_acte_ccam->rawStore();
    }

    $this->sendEvenementPMSI("CHPrimXMLEvenementsServeurActes", $codable);   
  }

  /**
   * @inheritdoc
   */
  function onAfterDelete(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    $receiver = $object->_receiver;

    switch ($object->_class) {
      case "CActeNGAP":
      case "CActeCCAM":
        if (!CAppUI::conf("sa CSa send_acte_immediately", $receiver->_ref_group->_guid)) {
          return false;
        }

        /** @var CActeCCAM|CActeNGAP $acte */
        $acte = $object;

        /** @var CCodable $codable */
        $codable = $acte->loadTargetObject();
        $codable->_receiver = $receiver;
        $sejour  = (!$codable instanceof CSejour) ? $codable->loadRefSejour(): $codable;

        // Cas ou le codable est un CDevisCodage ou un CModelCodage
        if (!$sejour || !$sejour instanceof CSejour) {
          return false;
        }

        // Destinataire gère seulement les non facturables
        if ($receiver->_configs["send_no_facturable"] == "0" && $sejour->facturable) {
          return false;
        }

        // Destinataire gère seulement les facturables
        if ($receiver->_configs["send_no_facturable"] == "2" && !$sejour->facturable) {
          return false;
        }

        $sejour->_NDA = null;
        $sejour->loadNDA($receiver->group_id);

        // Si le group_id du séjour est différent de celui du destinataire
        if ($sejour->group_id != $receiver->group_id) {
          return false;
        }

        $patient = $codable->loadRefPatient();
        $patient->_IPP = null;
        $patient->loadIPP($receiver->group_id);
        break;

      case "CExamIgs":
        if (!CAppUI::conf("sa CSa send_igs_immediately", $receiver->_ref_group->_guid)) {
          return false;
        }

        /** @var CExamIgs $exam_igs */
        $exam_igs = $object;
        $sejour = $exam_igs->loadRefSejour();

        $sejour->_NDA = null;
        $sejour->loadNDA($receiver->group_id);

        $patient = $sejour->loadRefPatient();
        $patient->_IPP = null;
        $patient->loadIPP($receiver->group_id);

        $this->sendEvenementPMSI("CHPrimXMLEvenementsServeurEtatsPatient", $exam_igs);

        return false;

      default:
        return false;
    }

    /** @var CPatient $patient */
    /** @var CSejour  $sejour */
    if (CAppUI::conf("sa CSa send_only_with_ipp_nda", $sejour->loadRefEtablissement()->_guid)) {
      if (!$patient->_IPP || !$sejour->_NDA) {
        throw new CMbException("CSaObjectHandler-send_only_with_ipp_nda", UI_MSG_ERROR);
      }
    }

    if (CAppUI::conf("sa CSa send_diags_with_actes", $sejour->loadRefEtablissement()->_guid)) {
      if ($sejour->DP || $sejour->DR || (count($sejour->loadRefDossierMedical()->_codes_cim) > 0)) {
        $sejour->_receiver = $receiver;

        $evt = (CAppUI::conf("hprimxml send_diagnostic") === "evt_serveuretatspatient") ?
          "CHPrimXMLEvenementsServeurEtatsPatient" : "CHPrimXMLEvenementsPmsi";

        $this->sendEvenementPMSI($evt, $sejour);
      }
    }

    if (CAppUI::conf("sa CSa send_acte_immediately", $receiver->_ref_group->_guid)
      && (!$object instanceof CActeCCAM && !$object instanceof CActeNGAP)
    ) {
      return false;
    }

    $codable->_ref_actes_ccam = array();
    $codable->_ref_actes_ngap = array();

    // On value les champs uniquement si on ajoute/modifie/supprime un acte CCAM/NGAP
    if ($object instanceof CActeNGAP) {
      $codable->_ref_actes_ngap = array($object);
    }
    elseif ($object instanceof CActeCCAM) {
      $codable->_ref_actes_ccam = array($object);
    }
    else {
      // Chargement des actes du codable
      $codable->loadRefsActes();
    }

    // Envoi des actes CCAM / NGAP
    if (empty($codable->_ref_actes_ccam) && empty($codable->_ref_actes_ngap)) {
      return false;
    }

    // Flag les actes CCAM en envoyés
    foreach ($codable->_ref_actes_ccam as $_acte_ccam) {
      $_acte_ccam->sent = 1;
      $_acte_ccam->rawStore();
    }

    $this->sendEvenementPMSI("CHPrimXMLEvenementsServeurActes", $codable);
  }
}
