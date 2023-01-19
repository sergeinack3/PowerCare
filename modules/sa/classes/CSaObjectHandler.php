<?php
/**
 * @package Mediboard\Sa
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Sa;

use Ox\Core\CAppUI;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CEAIObjectHandler;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Ox\Mediboard\Ssr\CRHS;

/**
 * Class CSaObjectHandler
 * SA Handler
 */

class CSaObjectHandler extends CEAIObjectHandler {
  /**
   * @var array
   */
  static $handled = array ("CSejour", "COperation", "CConsultation", "CRHS", "CActeNGAP", "CActeCCAM", "CExamIgs", "CDossierMedical");

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
      return !$object->_ignore_eai_handlers && in_array($object->_class, self::$handled);
  }

  /**
   * @inheritdoc
   */
  function onBeforeStore(CStoredObject $object) {
    if (!parent::onBeforeStore($object)) {
      return;
    }
  }

  /**
   * @inheritdoc
   */
  function onBeforeDelete(CStoredObject $object) {
    if (!parent::onBeforeDelete($object)) {
      return;
    }
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!parent::onAfterStore($object)) {
      return;
    }

    switch ($object->_class) {
      // CSejour 
      // Envoi des actes / diags soit quand le séjour est facturé, soit quand le sejour a une sortie réelle
      // soit quand on a la clôture sur le sejour
      case 'CSejour':
        /** @var CSejour $sejour */
        $sejour = $object;

        if ($sejour->_no_synchro_eai) {
          return;
        }
        $group = $sejour->loadRefEtablissement();
        $send_only_with_type = CAppUI::conf("sa CSa send_only_with_type", $group->_guid);
        if ($send_only_with_type && ($send_only_with_type != $sejour->type)) {
          return;  
        }

        // Envoi des diags DP et DR au fil de l'eau
        if (CAppUI::conf("sa CSa send_diag_immediately", $sejour->loadRefEtablissement())
            && ($sejour->fieldModified("DP") || $sejour->fieldModified("DR")) ||
          (($sejour->_old->DP && !$sejour->DP) || ($sejour->_old->DR && !$sejour->DR))) {

          $this->sendFormatAction("onAfterStore", $sejour);
          return;
        }

        $trigger = false;
        switch (CAppUI::conf("sa CSa trigger_sejour", $group->_guid)) {
          case 'sortie_reelle':
            if ($sejour->fieldModified('sortie_reelle') || isset($sejour->_force_sent) && $sejour->_force_sent === true) {
              $trigger = true;

              $this->sendFormatAction("onAfterStore", $sejour);

              if (CAppUI::conf("sa CSa facture_codable_with_sejour", $group->_guid)) {
                $sejour->facture = 1;
                $sejour->rawStore();
              }
            }
            break;
            
          case 'testCloture':
            if ($sejour->testCloture()) {
              $trigger = true;
              $this->sendFormatAction("onAfterStore", $sejour);
            }
            break;
            
          default:
            if ($sejour->fieldModified('facture', 1)) {
              $trigger = true;
              $this->sendFormatAction("onAfterStore", $sejour);
            }
            break;
        }

        if (!$trigger) {
          return;
        }

        if (CAppUI::conf("sa CSa send_actes_consult", $group->_guid) ) {
          if ($sejour->loadRefsConsultations()) {
            foreach ($sejour->_ref_consultations as $_consultation) {
              if (!$_consultation->sejour_id) {
                continue;
              }

              $sejour = $_consultation->loadRefSejour();
              $this->sendFormatAction("onAfterStore", $_consultation);

              if (CAppUI::conf("sa CSa facture_codable_with_sejour", $group->_guid)) {
                $_consultation->facture = 1;
                $_consultation->rawStore();
              }
            }
          }
        }

        if (CAppUI::conf("sa CSa send_actes_interv", $group->_guid)) {
          if ($sejour->loadRefsOperations()) {
            foreach ($sejour->_ref_operations as $_operation) {
              $this->sendFormatAction("onAfterStore", $_operation);

              if (CAppUI::conf("sa CSa facture_codable_with_sejour", $group->_guid)) {
                $_operation->facture = 1;
                $_operation->rawStore();
              }
            }
          }
        }

        break;
      
      // COperation
      // Envoi des actes soit quand l'interv est facturée, soit quand on a la clôture sur l'interv
      case 'COperation':
        /** @var COperation $operation */
        $operation = $object;

        if ($operation->_no_synchro_eai) {
          return;
        }

        $group = $operation->loadRefSejour()->loadRefEtablissement();


        switch (CAppUI::conf("sa CSa trigger_operation", $group->_guid)) {
          case 'testCloture':
            if ($operation->testCloture()) {
              $this->sendFormatAction("onAfterStore", $operation);
            }
            break;

          case 'sortie_reelle':
            return;

          default:
            if ($operation->fieldModified('facture', 1)) {
              $this->sendFormatAction("onAfterStore", $operation);
            }
            break;
        }
        break;
      
      // CConsultation
      // Envoi des actes dans le cas de la clôture de la cotation
      case 'CConsultation':
        /** @var CConsultation $consultation */
        $consultation = $object;

        if ($consultation->_no_synchro_eai) {
          return;
        }
        
        if (!$consultation->sejour_id) {
          return;
        }

        $group = $consultation->loadRefGroup();

        switch (CAppUI::conf("sa CSa trigger_consultation", $group->_guid)) {
          case 'facture':
            if ($consultation->fieldModified('facture', 1)) {
              $this->sendFormatAction("onAfterStore", $consultation);
            }
            break;

          case 'sortie_reelle':
            return;

          default:
            if ($consultation->fieldModified('valide', 1)) {
              $this->sendFormatAction("onAfterStore", $consultation);
            }
            break;
        }

        break;

      // CRHS
      // Envoi des actes CSARR
      case 'CRHS':
        /** @var CRHS $rhs */
        $rhs = $object;

        $group = $rhs->loadRefSejour()->loadRefEtablissement();
        if (!CAppUI::conf("sa CSa send_rhs", $group->_guid)) {
          return;
        }

        if ($rhs->fieldModified('facture', 1)) {
          $this->sendFormatAction("onAfterStore", $rhs);
        }
        break;

      case 'CActeCCAM':
        /** @var CActeCCAM $acte_ccam */
        $acte_ccam = $object;
        if ($acte_ccam->_no_synchro_eai) {
          return;
        }

        $this->sendFormatAction("onAfterStore", $acte_ccam);

        break;

      case 'CActeNGAP':
        /** @var CActeNGAP $acte_ngap */
        $acte_ngap = $object;

        if ($acte_ngap->_no_synchro_eai) {
          return;
        }

        $this->sendFormatAction("onAfterStore", $acte_ngap);

        break;

      case 'CExamIgs':
        /** @var CExamIgs $exam_igs */
        $exam_igs = $object;
        if ($exam_igs->_no_synchro_eai) {
          return;
        }
        $this->sendFormatAction("onAfterStore", $exam_igs);

        break;

      // Envoi des diags DAS au fil de l'eau
      case "CDossierMedical":
        /** @var CDossierMedical $dossier_medical */
        $dossier_medical = $object;

        if ($dossier_medical->object_class != "CSejour") {
          return;
        }

        /** @var CSejour $sejour */
        $sejour = $dossier_medical->loadTargetObject();

        if (!CAppUI::conf("sa CSa send_diag_immediately", $sejour->loadRefEtablissement())) {
          return;
        }

        // Envoi des diags au fil de l'eau
        if ($dossier_medical->fieldModified("codes_cim")) {
          $this->sendFormatAction("onAfterStore", $dossier_medical);
          return;
        }
        break;

      default:
        return;
    } 
  }

  /**
   * @inheritdoc
   */
  function onAfterDelete(CStoredObject $object) {
    if (!parent::onAfterStore($object)) {
      return;
    }

    switch ($object->_class) {
      case "CActeNGAP": case "CActeCCAM":
        /** @var CActeCCAM|CActeNGAP $object */
        if ($object->_no_synchro_eai) {
          return;
        }

        $acte = $object->_old;
        $this->sendFormatAction("onAfterDelete", $acte);

        break;

      case 'CExamIgs':
        /** @var CExamIgs $object */
        if ($object->_no_synchro_eai) {
          return;
        }

        $exam_igs = $object->_old;
        $this->sendFormatAction("onAfterDelete", $exam_igs);

        break;

      default:
        return;
    }
  }
}
