<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CSQLDataSource;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CPreferences;
use Ox\Mediboard\System\CSourceSMTP;
use phpmailerException;

/**
 * BMR et BHRe
 */
class CBMRBHRe extends CMbObject {
  /** @var integer Primary key */
  public $bmr_bhre_id;

  // DB Fields
  public $patient_id;
  public $bmr;
  public $bmr_debut;
  public $bmr_fin;
  public $bhre;
  public $bhre_debut;
  public $bhre_fin;
  public $hospi_etranger;
  public $hospi_etranger_debut;
  public $hospi_etranger_fin;
  public $rapatriement_sanitaire;
  public $ancien_bhre;
  public $bhre_contact;
  public $bhre_contact_debut;
  public $bhre_contact_fin;

  // Form fields
  public $_bmr_date;
  public $_bhre_date;
  public $_hospi_etranger_date;
  public $_rapatriement_sanitaire_date;
  public $_ancien_bhre_date;
  public $_bhre_contact_date;

  // References
  /** @var CPatient */
  public $_ref_patient;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "bmr_bhre";
    $spec->key   = "bmr_bhre_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                           = parent::getProps();
    $props["patient_id"]             = "ref class|CPatient notNull back|bmr_bhre";
    $props["bmr"]                    = "enum list|0|1|NR";
    $props["bmr_debut"]              = "date";
    $props["bmr_fin"]                = "date moreThan|bmr_debut";
    $props["bhre"]                   = "enum list|0|1|NR";
    $props["bhre_debut"]             = "date";
    $props["bhre_fin"]               = "date moreThan|bhre_debut";
    $props["hospi_etranger"]         = "enum list|0|1|NR";
    $props["hospi_etranger_debut"]   = "date";
    $props["hospi_etranger_fin"]     = "date moreThan|hospi_etranger_debut";
    $props["rapatriement_sanitaire"] = "enum list|0|1|NR";
    $props["ancien_bhre"]            = "enum list|0|1|NR";
    $props["bhre_contact"]           = "enum list|0|1|NR";
    $props["bhre_contact_debut"]     = "date";
    $props["bhre_contact_fin"]       = "date moreThan|bhre_contact_debut";

    return $props;
  }

  /**
   * Identifiant de dossier BHM BHRe lié à l'objet fourni.
   * Crée le dossier si nécessaire
   *
   * @param integer $patient_id Identifiant du patient
   *
   * @return integer Id du dossier BMR BHRe
   */
  static function dossierId($patient_id) {
    $dossier             = new CBMRBHRe();
    $dossier->patient_id = $patient_id;
    $dossier->loadMatchingObject();
    if (!$dossier->_id) {
      $dossier->store();
    }

    return $dossier->_id;
  }

  /**
   * Charge le patient associé au BMR BHRe
   *
   * @return CPatient
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef("patient_id", true);
  }

  /**
   * @inheritdoc
   */
  function store() {
    $alerte_mail = null;

    foreach (["bmr", "bhre", "bhre_contact"] as $_field) {
      if ($this->fieldModified($_field) && $this->$_field === "1") {
        $alerte_mail = $_field;
        break;
      }
    }

    if ($msg = parent::store()) {
      return $msg;
    }

    if ($alerte_mail) {
      $patient = $this->loadRefPatient();

      /** @var CSourceSMTP $exchange_source */
      $exchange_source = CExchangeSource::get("system-message", CSourceSMTP::TYPE);

      if ($exchange_source->_id) {
        $exchange_source->init();

        $curr_user = CMediusers::get();

        $exchange_source->setSenderNameFromUser($curr_user, true);

        $preference = new CPreferences();

        $where = [
          "key"     => "= 'alert_bmr_bhre'",
          "value"   => "= '1'",
          "user_id" => "IS NOT NULL",
        ];

        $user_ids = $preference->loadColumn("user_id", $where);

        $user = new CUser();

        $where = [
          "user_id"    => CSQLDataSource::prepareIn($user_ids),
          "user_email" => "IS NOT NULL",
        ];

        $emails = $user->loadColumn("user_email", $where);

        if (count($emails)) {
          foreach ($emails as $_email) {
            if (CMbString::checkEmailFormat($_email)) {
              try {
                $exchange_source->addTo($_email);
              }
              catch (phpmailerException $e) {
                CAppUI::displayAjaxMsg($e->errorMessage(), UI_MSG_WARNING);
              }
            }
          }

          $exchange_source->setSubject(CAppUI::tr("CBMRBHRE-subject_alert_mail", $patient->_view));
          $exchange_source->setBody(CAppUI::tr("CBMRBHRE-body_alert_mail", $patient->_view, CAppUI::tr("CBMRBHRe-$alerte_mail")));

          $mail = $exchange_source->createUserMail($curr_user->_id);

          try {
            $exchange_source->send();

            if ($mail) {
              $mail->sent = 1;
              $mail->store();
            }

            CAppUI::displayAjaxMsg("Message envoyé");
          }
          catch (phpmailerException $e) {
            CAppUI::displayAjaxMsg($e->errorMessage(), UI_MSG_WARNING);
          }
        }
      }
    }

    return null;
  }

  /**
   * @inheritdoc
   */
  function fillTemplate(&$template) {
    $this->fillLimitedTemplate($template);
  }

  /**
   * @inheritdoc
   */
  function fillLimitedTemplate(&$template) {
    $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

    $champ = CAppUI::tr('CBMRBHRe');
    $template->addProperty("$champ - " . CAppUI::tr("CBMRBHRe-bmr"), CAppUI::tr("CBMRBHRe.bmr.$this->bmr"));
    $template->addProperty("$champ - " . CAppUI::tr("CBMRBHRe-bhre"), CAppUI::tr("CBMRBHRe.bhre.$this->bhre"));
    $template->addProperty(
      "$champ - " . CAppUI::tr("CBMRBHRe-hospi_etranger"),
      CAppUI::tr("CBMRBHRe.hospi_etranger.$this->hospi_etranger")
    );
    $template->addProperty(
      "$champ - " . CAppUI::tr("CBMRBHRe-rapatriement_sanitaire"),
      CAppUI::tr("CBMRBHRe.rapatriement_sanitaire.$this->rapatriement_sanitaire")
    );
    $template->addProperty("$champ - " . CAppUI::tr("CBMRBHRe-ancien_bhre"), CAppUI::tr("CBMRBHRe.ancien_bhre.$this->ancien_bhre"));
    $template->addProperty("$champ - " . CAppUI::tr("CBMRBHRe-bhre_contact"), CAppUI::tr("CBMRBHRe.bhre_contact.$this->bhre_contact"));
    $template->addDateProperty("$champ - " . CAppUI::tr("CBMRBHRe-bhre_contact_debut"), $this->bhre_contact_debut);
    $template->addDateProperty("$champ - " . CAppUI::tr("CBMRBHRe-bhre_contact_fin"), $this->bhre_contact_fin);

    $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
  }
}
