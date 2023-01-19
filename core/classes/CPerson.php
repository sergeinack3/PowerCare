<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Admin\Rgpd\IRGPDCompliant;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CSalutation;
use Ox\Mediboard\System\CFirstNameAssociativeSex;

/**
 * Class to harmonize person fields and represent a person
 */
class CPerson extends CMbObject implements IRGPDCompliant {
  public $_p_city;
  public $_p_postal_code;
  public $_p_street_address;
  public $_p_country;
  public $_p_phone_number;
  public $_p_fax_number;
  public $_p_mobile_phone_number;
  public $_p_email;
  public $_p_first_name;
  public $_p_last_name;
  public $_p_birth_date;
  public $_p_maiden_name;
  public $_p_international_phone;
  public $_p_international_mobile_phone;
  public $_p_phone_area_code;

  public $_initial;

  public $_starting_formula;
  public $_closing_formula;
  public $_tutoiement;

  /** @var CRGPDConsent */
  public $_rgpd_consent;

  /** @var CRGPDManager|null */
  public $_rgpd_manager;

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["_p_city"]                       = "str";
    $props["_p_postal_code"]                = "str";
    $props["_p_street_address"]             = "str";
    $props["_p_country"]                    = "str";
    $props["_p_phone_number"]               = "phone";
    $props["_p_fax_number"]                 = "phone";
    $props["_p_mobile_phone_number"]        = "phone";
    $props["_p_email"]                      = "str";
    $props["_p_first_name"]                 = "str";
    $props["_p_last_name"]                  = "str";
    $props["_p_birth_date"]                 = "birthDate";
    $props["_p_maiden_name"]                = "str";
    $props['_p_international_phone']        = 'str';
    $props['_p_international_mobile_phone'] = 'str';
    $props['_p_phone_area_code']            = 'str maxLength|2';
    $props['_initial']                      = 'str';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_initial = CMbString::makeInitials($this->_p_first_name, "-");
    $this->_initial .= CMbString::makeInitials($this->_p_last_name);
  }

  /**
   * @inheritdoc
   */
  function store() {
    // sexe undefined
    $this->guessSex();

    // Cas particulier de l'assuré qui est stocké dans la même table que le patient...
    if ($this->_class == "CPatient") {
      $this->guessSex("assure_sexe", "assure_prenom");
    }

    return parent::store();
  }

  /**
   * Return the sex field of the herited class
   *
   * @return null|string
   */
  function getSexFieldName() {
    return null;
  }

  /**
   * Return the prenom field of the herited class
   *
   * @return null|string
   */
  function getPrenomFieldName() {
    return null;
  }

  /**
   * Return the nom field of the herited class
   *
   * @return null|string
   */
  function getNomFieldName() {
    return null;
  }

  /**
   * Return the naissance field of the herited class
   *
   * @return null|string
   */
  function getNaissanceFieldName() {
    return null;
  }

  /**
   * @inheritdoc
   */
  function getEmail() {
    return ($this->_p_email && CMbString::checkEmailFormat($this->_p_email)) ? $this->_p_email : null;
  }

  /**
   * Map the class variable with CPerson variable
   *
   * @return void
   */
  function mapPerson() {
  }

  public function isPraticien(): bool
  {
      return false;
  }

  /**
   * Set starting and closing formulas
   *
   * @param integer|null $user_id Given owner id
   *
   * @return void
   */
  function loadSalutations($user_id = null) {
    if (!$this->_id) {
      return;
    }

    $salutation               = new CSalutation();
    $salutation->owner_id     = ($user_id) ?: CMediusers::get()->_id;
    $salutation->object_class = $this->_class;
    $salutation->object_id    = $this->_id;
    $owner                    = CMediusers::get($salutation->owner_id);

    if ($salutation->loadMatchingObject()) {
      $this->_starting_formula = $salutation->starting_formula;
      $this->_closing_formula  = $salutation->closing_formula;
      $this->_tutoiement       = $salutation->tutoiement;
    }
    else {
        $sexe_field = $this->getSexFieldName();
        //Verifie si le on veut une salutation pour un praticien et si l'utilisateur est un praticien
        if ($this->isPraticien() && $owner->isPraticien()) {
            if ($sexe_field && $this->{$sexe_field} === 'f') {
                $this->_starting_formula = CAppUI::tr('CSalutation-starting_formula_f|default');
            } else {
                $this->_starting_formula = CAppUI::tr('CSalutation-starting_formula|default');
            }
            $this->_closing_formula = CAppUI::tr('CSalutation-closing_formula|default');
        } else {
            if ($sexe_field && $this->{$sexe_field} === 'f') {
                $this->_starting_formula = CAppUI::tr('CSalutation-starting-formula-f-non-praticien');
            } else {
                $this->_starting_formula = CAppUI::tr('CSalutation-starting-formula-m-non-praticien');
            }
            $this->_closing_formula = CAppUI::tr('CSalutation-closing-formula-non-praticien');
        }
    }

    return;
  }

  /**
   * Guess sexe by firstname
   *
   * @return void
   */
  function guessSex($field_sexe = null, $field_prenom = null) {
    $field_sexe   = $field_sexe ?: $this->getSexFieldName();
    $field_prenom = $field_prenom ?: $this->getPrenomFieldName();

    // No sex field defined
    if (!$field_sexe) {
      return;
    }

    // No prenom field defined
    if (!$field_prenom) {
      return;
    }

    // Sex already defined
    if ($this->{$field_sexe} && $this->{$field_sexe} != "u") {
      return;
    }

    // No prenom field valued
    if (!$this->{$field_prenom}) {
      return;
    }

    $sex_found = CFirstNameAssociativeSex::getSexFor($this->{$field_prenom});
    if ($sex_found && $sex_found != "u") {
      $this->{$field_sexe} = $sex_found;
    }
  }

  /**
   * @inheritDoc
   */
  public function setRGPDConsent(CRGPDConsent $consent = null) {
    return $this->_rgpd_consent = $consent;
  }

  /**
   * @inheritdoc
   */
  function shouldAskConsent() {
    return true;
  }

  /**
   * @inheritdoc
   */
  function canAskConsent() {
    return ($this->shouldAskConsent() && ($this->getEmail() !== null));
  }

  /**
   * @inheritDoc
   */
  function getFirstNameField() {
    return $this->getPrenomFieldName();
  }

  /**
   * @inheritDoc
   */
  function getLastNameField() {
    return $this->getNomFieldName();
  }

  /**
   * @inheritDoc
   */
  function getBirthdateField() {
    return $this->getNaissanceFieldName();
  }

  /**
   * @inheritdoc
   */
  function fillTemplate(&$template) {
    $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

    $manager = new CRGPDManager(CGroups::loadCurrent()->_id);

    if ($manager->canNotify($this->_class)) {
      $smarty = new CSmartyDP('modules/admin');
      $smarty->assign('manager', $manager);
      $smarty->assign('object_class', $this->_class);
      $smarty->assign('stylized', '0');

      if ($this->_id && $manager->canNotifyWithActions($this->_class)) {
        $consent = $manager->getConsentForObject($this);
        $this->setRGPDConsent($consent);
        $smarty->assign('token', $consent->getResponseToken());
      }

      $content = $smarty->fetch('inc_vw_rgpd_document.tpl');
      $template->addProperty(CAppUI::tr('CRGPDConsent') . ' - ' . CAppUI::tr("CRGPDConsent.object_class.{$this->_class}") . ' Document', $content, null, false);
    }

    $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
  }

  /**
   * @inheritdoc
   */
  function loadView() {
    parent::loadView();

    $manager = new CRGPDManager(CGroups::loadCurrent()->_id);

    if ($manager->isEnabledFor($this)) {
      $consent = $manager->getConsentForObject($this);
      $this->setRGPDConsent($consent);
    }

    $this->_rgpd_manager = $manager;
  }
}
