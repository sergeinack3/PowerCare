/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// $Id: $

/** TODO: Factoriser ceci pour ne pas avoir a etendre l'objet (sinon Patient.create est ecrasé) */
Patient = Object.extend({
  tabs:                      null,
  modulePatient:             'patients',
  form_search:               'find',
  adult_age:                 null,
  anonymous_sexe:            null,
  anonymous_naissance:       null,
  copying_prenom:            null,
  origin_mode_obtention:     null,
  ref_pays:                  null,

  assure_values: [
    'nom', 'prenom', 'prenoms', 'nom_jeune_fille',
    'sexe', 'naissance',
    'cp_naissance', 'pays_naissance_insee', 'lieu_naissance', 'profession',
    'adresse', 'rang_naissance'
  ],

  view:                function (patient_id) {
    new Url(this.modulePatient, 'vw_full_patients', 'tab').addParam('patient_id', patient_id).redirectOpener();
  },
  viewModal:           function (patient_id, onclose, fullPage = false) {
    var options = {
      width:   '90%',
      height:  '90%',
      onClose: onclose
    }
    if (fullPage) {
      options.width = options.height = '100%';
    }
    new Url(this.modulePatient, 'vw_full_patients').addParam('patient_id', patient_id).modal(options);
  },
  history:             function (patient_id) {
    new Url('patients', 'vw_history').addParam("patient_id", patient_id).popup(600, 500, 'patient history');
  },
  print:               function (patient_id) {
    new Url('patients', 'print_patient').addParam('patient_id', patient_id).popup(700, 550, 'Patient');
  },
  showDossierMedical:  function (patient_id) {
    new Url('cabinet', 'listAntecedents')
      .addParam('patient_id', patient_id)
      .addParam('sejour_id', '') // A passer car le script récupère sinon le sejour_id en session
      .addParam('show_header', 1)
      .modal({width: '80%', height: '80%'});
  },
  showSummary:         function (patient_id) {
    new Url('cabinet', 'vw_resume')
      .addParam("patient_id", patient_id)
      .popup(800, 500, 'Summary' + (Preferences.multi_popups_resume == '1' ? patient_id : null));
  },
  create:              function (form) {
    new Url('patients', 'vw_edit_patients', 'tab').addParam('patient_id', 0).addParam('useVitale', $V(form.useVitale)).addParam('name', $V(form.nom)).addParam('firstName', $V(form.prenom)).addParam('naissance_day', $V(form.Date_Day)).addParam('naissance_month', $V(form.Date_Month)).addParam('naissance_year', $V(form.Date_Year)).redirect();
  },
  createModal:         function (form, callback, onclose) {
    new Url('patients', 'vw_edit_patients').addParam('patient_id', 0).addParam('useVitale', $V(form.useVitale)).addParam('name', $V(form.nom)).addParam('firstName', $V(form.prenom)).addParam('naissance_day', $V(form.Date_Day)).addParam('naissance_month', $V(form.Date_Month)).addParam('naissance_year', $V(form.Date_Year)).addParam('sexe', $V(form.sexe)).addParam('callback', callback).addParam('modal', 1).addParam('nom_jeune_fille',  $V(form.nom_jeune_fille)).modal({
      width:   '90%',
      height:  '90%',
      onClose: onclose
    });
  },
  edit:                function (patient_id, use_vitale) {
    new Url('patients', 'vw_edit_patients', 'tab').addParam('patient_id', patient_id).addParam('useVitale', use_vitale).redirectOpener();
  },
  editModal:           function (patient_id, use_vitale, callback, onclose, fragment, validate_identity) {
    new Url('patients', 'vw_edit_patients')
      .addParam('patient_id', patient_id)
      .addParam('use_vitale', use_vitale)
      .addParam('callback', callback)
      .addParam('validate_identity', validate_identity)
      .setFragment(fragment)
      .modal(
        {
          width:   '90%',
          height:  '90%',
          onClose: onclose
        }
      );
  },

  /**
   * Vérification du lieu de naissance :
   *  - pays obligatoire
   *  - commune obligatoire si le pays sélectionné est la france
   *
   * @param form
   * @returns {boolean}
   */
  checkLieuNaissance: function(form, prefixElement = '') {
    let pays_naissance      = $V(form.elements[`${prefixElement}_pays_naissance_insee`]).toLowerCase(),
        commune_naissance   = $V(form.elements[`${prefixElement}lieu_naissance`]),
        source_cp_naissance = $V(form.elements[`${prefixElement}cp_naissance`]),
        code_insee          = $V(form.elements[`${prefixElement}_code_insee`]);

    if (code_insee === '99999') {
      $V(form.elements[`${prefixElement}cp_naissance`],'99999');
      $V(form.elements[`${prefixElement}_pays_naissance_insee`],'');
      $V(form.elements[`${prefixElement}lieu_naissance`],'');
      source_cp_naissance = $V(form.elements[`${prefixElement}cp_naissance`]);
    } else if (source_cp_naissance === '99999') {
      $V(form.elements[`${prefixElement}_code_insee`],'99999');
      $V(form.elements[`${prefixElement}_pays_naissance_insee`],'');
      $V(form.elements[`${prefixElement}lieu_naissance`],'');
    }

    if (source_cp_naissance === '99999') {
      return true;
    }

    if ((!commune_naissance || !source_cp_naissance) && !pays_naissance) {
      alert($T('CPatient-Pays naissance mandatory'));
      form.elements[`${prefixElement}_pays_naissance_insee`].tryFocus();
      return false;
    }

    if (!commune_naissance && (pays_naissance === 'france')) {
      alert($T('CPatient-Commune naissance mandatory'));
      form.elements[`${prefixElement}lieu_naissance`].tryFocus();
      return false;
    }

    return true;
  },

  copyPrenom: function(element, from_justif) {
    if (Patient.copying_prenom) {
      return;
    }

    Patient.copying_prenom = true;

    var field_prenoms = 'prenoms';

    if (from_justif) {
      field_prenoms = '_source_' + field_prenoms;
    }

    $V(element.form.elements[field_prenoms], $V(element));

    Patient.copying_prenom = false;
  },

  toggleCopyPrenom: (element) => {
    var button = $('copy_prenom');

    button.show();

    if ($V(element)) {
      button.hide();
    }
  },

  confirmCreation:     function (form) {
    if (!Patient.checkLieuNaissance(form)) {
      return false;
    }

    if (!checkForm(form)) {
      return false;
    }

    if (!Patient.checkBirthNameMatchesNames(true)) {
      return false
    }

    if (!Patient.checkBirthdate(form) && $V(form.modal)) {
      return false;
    }

    SiblingsChecker.submit = 1;
    SiblingsChecker.request(form);
    return false;
  },
  confirmPurge:        function (form, pat_view) {
    if (confirm($T('CPatient-Alert confirm purge'))) {
      $V(form._purge, "1");
      confirmDeletion(form, {
        typeName: 'le patient',
        objName:  pat_view
      });
    }
  },
  exportVcard:         function (patient_id) {
    new Url('patients', 'ajax_export_vcard').addParam('patient_id', patient_id).addParam('suppressHeaders', 1).pop(700, 550, 'Patient');
  },
  openINS:             function ($id) {
    new Url('patients', 'ajax_history_ins')
      .addParam('patient_id', $id)
      .requestModal();
  },
  doMerge:             function (oForm) {
    new Url('system', 'object_merger')
      .addParam('objects_class', 'CPatient')
      .addParam('objects_id', $V(oForm['objects_id[]']).join('-'))
      .popup(800, 600, 'merge_patients');
  },
  doLink:              function (oForm) {
    new Url('patients', 'do_link', 'dosql')
      .addParam('objects_id', $V(oForm['objects_id[]']).join('-'))
      .requestUpdate('systemMsg', {
        method: 'post'
      });
  },
  doPurge:             function (patient_id) {
    new Url(this.modulePatient, 'vw_idx_patients')
      .addParam('dosql', 'do_patients_aed')
      .addParam('del', 1)
      .addParam('_purge', 1)
      .addParam('patient_id', patient_id)
      .requestUpdate('systemMsg', {
        method: 'post'
      });
  },
  isMobilePhone:       function (phoneNumber) {
    var firstDigits = phoneNumber.substring(0, 2);
    return (firstDigits == '06' || firstDigits == '07');
  },
  checkMobilePhone:    function (element) {
    var div = $('mobilePhoneFormat');
    var phoneNumber = element.value.replace(/[_ ]/g, '');
    if (phoneNumber.length < 2 || Calendar.ref_pays != 1) {
      div.hide();
    } else {
      Patient.isMobilePhone(phoneNumber) ? div.hide() : div.show();
    }
  },
  checkNotMobilePhone: function (element) {
    var div = $('phoneFormat');
    var phoneNumber = element.value.replace(/[_ ]/g, '');
    if (phoneNumber.length < 2 || Calendar.ref_pays != 1) {
      div.hide();
    } else {
      Patient.isMobilePhone(phoneNumber) ? div.show() : div.hide();
    }
  },

  toggleSearch: function () {
    $$('.field_advanced').invoke('toggle');
    $$('.field_basic').invoke('toggle');
  },

  togglePraticien: function () {
    var praticien = getForm(Patient.form_search).prat_id;
    var praticien_message = $('prat_id_message');
    var enough = Patient.checkEnoughTraits();

    praticien.setVisible(enough);
    praticien_message.setVisible(!enough);

    if (!enough) {
      $V(praticien, '');
    }
  },

  checkEnoughTraits: function () {
    var form = getForm(Patient.form_search);

    return $V(form.nom).length >= 2 ||
      $V(form.prenom).length >= 2 ||
      $V(form.cp).length >= 2 ||
      $V(form.ville).length >= 2 ||
      $V(form.Date_Year) ||
      ($V(form.Date_Day) && $V(form.Date_Month) && $V(form.Date_Year));
  },

  fillBMRBHeId: function (bmr_bhe_id) {
    $V(getForm('editBMRBHRe').bmr_bhre_id, bmr_bhe_id);
  },

  showFamilyLinkWithPatient: function (parent_id_1, parent_id_2, patient_id) {
    new Url('patients', 'vw_family_link')
      .addParam('patient_id', patient_id)
      .addParam('parent_id_1', parent_id_1)
      .addParam('parent_id_2', parent_id_2)
      .requestJSON(function (families) {
        var show_family = $('show_family');
        if (!show_family) {
          return;
        }

        var array_size = Object.keys(families).length;
        var comma = ", ";

        if (array_size) {
          var counter = 1;
          Object.keys(families).each(function (id) {
            var family = families[id];

            if (counter == array_size) {
              comma = "";
            }

            var elementDOM = DOM.span({
              className:   '',
              onmouseover: "ObjectTooltip.createEx(this, 'CPatient-" + family["id"] + "')"
            }, family["view"] + comma);

            counter++;

            show_family.insert(elementDOM);
          });
        }
      });
  },

  callbackFamilyLink: function (patient_family_link_id) {
    $V(getForm('FrmPatientFamily').patient_family_link_id, patient_family_link_id);
  },

  getCoordinatesParent: function (parent_id) {
    var form = getForm('editFrm');
    new Url('patients', 'ajax_coordonnees_parent')
      .addParam('parent_id', parent_id)
      .requestJSON(function (coordonnees) {
        $V(form.adresse, coordonnees['adresse']);
        $V(form.cp, coordonnees['cp']);
        $V(form.ville, coordonnees['ville']);
        $V(form.pays, coordonnees['pays']);

        if (coordonnees['adresse']) {
          SystemMessage.notify('<div class="info">' + $T('CPatient-msg-Patient coordinates copied') + '</div><div class="warning">' + $T('CPatient-msg-Do not forget to save') + '</div>', false);
        }
      });
  },

  getAntecedentParents: function (patient_id, context_class, context_id) {
    new Url('patients', 'ajax_antecedents_parents')
      .addParam('patient_id', patient_id)
      .addParam('context_class', context_class)
      .addParam('context_id', context_id)
      .requestModal('90%', '90%');
  },

  sendAntecedentsParent: function (object_class, object_id) {
    var atcds_selected = [];

    $('antecedents_parent1').select('input[name=antecedent_parent1]:checked').each(function (elt) {
      var tbody = elt.up('tbody');
      atcds_selected.push(tbody.getAttribute('id'));
    });

    $('antecedents_parent2').select('input[name=antecedent_parent2]:checked').each(function (elt) {
      var tbody = elt.up('tbody');
      atcds_selected.push(tbody.getAttribute('id'));
    });

    new Url('patients', 'controllers/do_send_antecedents')
      .addParam('atcds_selected[]', atcds_selected, true)
      .addParam('object_class', object_class)
      .addParam('object_id', object_id)
      .requestUpdate("systemMsg", {
        onComplete: function () {
          Control.Modal.close();
          if (window.DossierMedical) {
            DossierMedical.reloadDossierPatient();
          }
        }
      });

    return false;
  },

  copyAssureValues: function (element) {
    // Hack pour gérer les form fields
    var sPrefix = element.name[0] == "_" ? "_assure" : "assure_";
    var eOther = element.form[sPrefix + element.name];

    if (element.name === 'naissance') {
      $V(element.form['assure_naissance_amo'], $V(element));
    }

    // Copy value
    $V(eOther, $V(element));

    // Radio buttons seem to be null, et valuable with $V
    if (element.type != 'radio') {
      eOther.fire("mask:check");
    }
  },

  copyIdentiteAssureValues: function (element) {
    if (element.form.qual_beneficiaire.value === '00') {
      this.copyAssureValues(element);
    }
  },

  delAssureValues: function () {
    var form = getForm('editFrm');
    this.assure_values.each(function (_input_name) {
      $V(form.elements['assure_' + _input_name], '');
    });
  },

  copieAssureValues: function () {
    var form = getForm('editFrm');

    this.assure_values.each(function (_input_name) {
      $V(form.elements['assure_' + _input_name], $V(form.elements[_input_name]));

      if (_input_name === 'naissance') {
        $V(form.elements['assure_' + _input_name + '_amo'], $V(form.elements[_input_name]));
      }
    });
  },

  loadDocItems: function (patient_id) {
    new Url('files', 'httpreq_vw_listfiles')
      .addParam('selClass', 'CPatient')
      .addParam('selKey', patient_id)
      .requestUpdate('listView');
  },

  reloadListFileEditPatient: function (action, category_id) {
    if (!window.reloadListFile) {
      return;
    }
    reloadListFile(action, category_id);
  },

  calculFinAmo: function () {
    var form = getForm("editFrm");
    var sDate = $V(form.fin_amo);

    if ($V(form.c2s) === 1 && sDate === "") {
      date = new Date;
      date.addDays(365);
      $V(form.fin_amo, date.toDATE());
      $V(form.fin_amo_da, date.toLocaleDate());
    }
  },

  checkFinAmo: function () {
    var form = getForm("editFrm");
    var fin_amo = $V(form.fin_amo);
    var warning = $("fin_amo_warning");
    var tab = $$("#tab-patient a[href='#beneficiaire']")[0];

    if (fin_amo && fin_amo < (new Date()).toDATE()) {
      warning.show();
      tab.addClassName("wrong");
    } else {
      warning.hide();
      tab.removeClassName("wrong");
    }
  },

  toggleActivitePro: function (value) {
    $$('.activite_pro').invoke(value != '' ? 'show' : 'hide');
  },

  selectFirstEnabled: function (select) {
    var found = false;
    $A(select.options).each(function (o, i) {
      if (!found && !o.disabled && o.value != '') {
        $V(select, o.value);
        found = true;
      }
    });
  },

  disableOptions: function (select, list) {
    $A(select.options).each(function (o) {
      o.disabled = list.include(o.value);
    });

    if (select.value == '' || select.options[select.selectedIndex].disabled) {
      this.selectFirstEnabled(select);
    }
  },

  changeCivilite: function (assure) {
    var form = getForm('editFrm');
    var civilite = null;
    var valueSexe = null;
    var valueNaissance = null;

    if (assure) {
      civilite = 'assure_civilite';
      valueSexe = $V(form.assure_sexe);
      valueNaissance = $V(form.assure_naissance);
    } else {
      civilite = 'civilite';
      valueSexe = $V(form.sexe);
      valueNaissance = $V(form.naissance);
    }

    switch (valueSexe) {
      case 'm':
        this.disableOptions(form[civilite], $w('mme mlle vve'));
        break;

      case 'f':
        this.disableOptions(form[civilite], $w('m'));
        break;

      default:
        this.disableOptions(form[civilite], $w(''));
        $V(form[civilite], '');
        break;
    }

    if (valueNaissance) {
      var date = new Date();
      var naissance = valueNaissance.split('/')[2];
      if (((date.getFullYear() - this.adult_age) <= naissance) && (naissance <= (date.getFullYear()))) {
        $V(form[civilite], "enf");
      }
    }
  },

  resetFieldsForAnonymous: function () {
    var form = getForm('editFrm');

    $V(form.sexe, 'm');
    if (this.anonymous_sexe) {
      $V(form.sexe, this.anonymous_sexe);
    }

    $V(form.naissance, '1970-01-01');
    if (this.anonymous_naissance) {
      $V(form.naissance, this.anonymous_naissance);
    }

    $V(form.civilite, '');
    $V(form.situation_famille, 'S');
    $V(form.mdv_familiale, '');
    $V(form.condition_hebergement, '');
    $V(form.rang_naissance, 1);
    $V(form.cp_naissance, '');
    $V(form.lieu_naissance, '');
    $V(form._pays_naissance_insee, '');
    $V(form.niveau_etudes, '');
    $V(form.activite_pro, '');
    $V(form.profession, '');
    $V(form._csp_view, '');
    $V(form.fatigue_travail, '');
    $V(form.travail_hebdo, '');
    $V(form.transport_jour, '');
    $V(form.matricule, '');
    $V(form.qual_beneficiaire, '00');
    form.tutelle[0].checked = true;
    form.don_organes[0].checked = true;
    form.directives_anticipees[2].checked = true;
    $V(form.__vip, '');
    $V(form.deces, '');
    $V(form.adresse, '');
    $V(form.cp, '');
    $V(form.ville, '');
    $V(form.pays, '');
    $V(form.phone_area_code, '');
    $V(form.tel, '');
    $V(form.tel2, '');
    $V(form.__allow_sms_notification, '');
    $V(form.tel_pro, '');
    $V(form.tel_autre, '');
    $V(form.tel_autre_mobile, '');
    $V(form.email, '');
    $V(form.__allow_email, '');
    $V(form.rques, '');
  },

  anonymous: function () {
    $V("editFrm_nom", "anonyme");
    $V("editFrm_prenom", "anonyme");
    $V("editFrm_nom_jeune_fille", "anonyme");

    this.resetFieldsForAnonymous();
  },

  checkDoublon: function () {
    var form = getForm("editFrm");
    if ($V(form.nom_jeune_fille) && $V(form.prenom) && $V(form.naissance)) {
      SiblingsChecker.request(form);
    }
  },

  refreshInfoTutelle: function (tutelle) {
    new Url('patients', 'ajax_check_correspondant_tutelle')
      .addParam('patient_id', $V(getForm('editFrm').patient_id))
      .addParam('tutelle', tutelle)
      .requestUpdate('alert_tutelle');
  },

  accessibilityData: function () {
    new Url('patients', 'ajax_acces_patient')
      .addParam('patient_id', $V(getForm('editFrm').patient_id))
      .requestModal('70%', '70%');
  },

  showAdvanceDirectives: function () {
    new Url('patients', 'vw_list_directives_anticipees')
      .addParam('patient_id', $V(getForm('editFrm').patient_id))
      .requestModal(
        '70%',
        '70%',
        {
          onClose: function () {
            var warningExists = ($$('.no-directives').length > 0);
            AnticipatedDirectives.number_directives = $$('.a-directive').length;

            if (AnticipatedDirectives.number_directives === 0 && !warningExists) {
              AnticipatedDirectives.addWarningNoDirectives(true);
            }
            else if (AnticipatedDirectives.number_directives > 0 && warningExists) {
              AnticipatedDirectives.removeWarningNoDirectives();
            }
          }
        }
      );
  },

  checkAdvanceDirectives: function (elt, forceDisplay) {
    if (elt.value == 1) {
      this.showAdvanceDirectives();
    }
    else {
      AnticipatedDirectives.removeWarningNoDirectives();
    }
  },

  /**
   * Check if the birthdate is correct
   *
   * @param form
   * @returns {boolean}
   */
  checkBirthdate: function (form) {
    var current_year = new Date().getFullYear();
    var birthdate = $V(form.naissance);
    var birthdate_year = new Date(birthdate).getFullYear();

    if (birthdate_year > current_year) {
      alert($T('CPatient-msg-You cannot enter a date of birth greater than the current year'));
      return false;
    }

    return true;
  },

  addJustificatif: function(patient_id) {
    var form = getForm('editFrm');
    var status_identity = null;

    if ($V(form._fictif) === '1') {
      status_identity = 'fictif';
    }
    else if ($V(form._douteux) === '1') {
      status_identity = 'douteux';
    }

    if (status_identity
      && !confirm($T('CPatient-Warning about adding proof on special identity', $T('CPatient-_' + status_identity + '-alt')))) {
      return;
    }

    new Url('patients', 'addJustificatif')
      .addParam('patient_id', patient_id)
      .requestModal(800, 780);
  },

  submitJustificatif: function() {
    var form_from = getForm('addJustificatif');
    var form_to   = getForm('editFrm');

    if (!form_from || !form_to) {
      return false;
    }

    if (!checkForm(form_from)) {
      return false;
    }

    if (($V(form_to._douteux) === '1') || ($V(form_to._fictif) === '1')) {
      if (!confirm($T('CPatient-Confirm provisoire status will be kept despite adding proof'))) {
        Control.Modal.close();
        return false;
      }
    }

    // Vérification de la case à cocher "Valider l'identité"
    let select_proof = form_from._identity_proof_type_id;
    let trust_level = select_proof.options[select_proof.selectedIndex].dataset.trustLevel;

    if (trust_level === '3' && !form_from.___source__validate_identity.checked) {
      if (!confirm($T('CSourceIdentite-Confirm adding proof without validating'))) {
        return false;
      }
    }

    $V(form_to.elements['_mode_obtention'], 'manuel');
    $V(form_to.elements['_identity_proof_type_id'], $V(form_from.elements['_identity_proof_type_id']));
    $V(form_to.elements['_source__date_fin_validite'], $V(form_from.elements['_source__date_fin_validite']));
    $V(form_to.elements['_source__validate_identity'], $V(form_from.elements['_source__validate_identity']));
    $V(form_to._copy_file_id, $V(form_from._copy_file_id));

    var submit_form = $V(form_to.patient_id) !== '';

    [
      'nom_jeune_fille', 'prenom', 'prenoms',
      'naissance', 'sexe', 'civilite',
      '_pays_naissance_insee', 'cp_naissance', 'lieu_naissance', 'commune_naissance_insee'
    ].each(function(_input_name) {
        var input_name_source = '_source_' + _input_name;

        if (!form_from.elements[input_name_source]
            || !form_to.elements[input_name_source] || !form_to.elements[_input_name]) {
          return;
        }

        if ($V(form_from.elements[input_name_source])) {
          // Copie des champs dans les inputs hidden (modification du dossier patient)
          if (submit_form) {
            $V(form_to.elements[input_name_source], $V(form_from.elements[input_name_source]));
              if (
                  [
                      '_pays_naissance_insee', 'cp_naissance', 'lieu_naissance', 'commune_naissance_insee'
                  ].include(_input_name)
                  && $V(form_to.elements[_input_name]) === '') {
                  // Si les champs lieu de naissance sont vides alors que le patient existe, on remplit quand même
                  $V(form_to.elements[_input_name], $V(form_from.elements[input_name_source]));
              }
          }
          // ou visibles (création du dossier patient)
          else {
            $V(form_to.elements[_input_name], $V(form_from.elements[input_name_source]));
          }
        }
    });

    if (submit_form && !checkForm(form_to)) {
      return false;
    }

    if (!$V(form_to._copy_file_id) && !form_to.elements['formfile[]']) {
      if ($V(form_to.modal) === '1') {
        var img = form_from.down('img');

        if (img) {
          form_to.insert(DOM.input({
            type:        'hidden',
            name:        'formfile[]',
            value:       'Paper.jpg',
            'data-blob': 'blob'
          })
            .store("blob", IdInterpreter.dataURItoBlob(img.src)));
        }
      } else {
        var input_file = form_from.down("input[type=file][name='formfile[]']");

        if ($V(input_file)) {
          input_file = input_file.remove();
          input_file.hide();

          form_to.insert(input_file);
        }
      }
    }

    if (submit_form) {
      $V(form_to._map_source_form_fields, '1');

      return form_to.onsubmit();
    }

    Control.Modal.close();

    return false;
  },

  checkCentreGestionnaire: (input) => {
      if ($V(input).length === 3 && input !== document.activeElement) {
          $V(input, '0' + $V(input), false);
      }
  },

  warningIdentity: (input, status_identity) => {
    if ($V(input) === '0') {
      return;
    }

    if (!confirm($T('CPatient-Confirm special identity', $T('CPatient-_' + status_identity + '-alt')))) {
      $V(input, '0');
      input.form.elements['___' + status_identity].checked = false;
      $V(input.form._mode_obtention, Patient.origin_mode_obtention);
    } else {
      $V(input.form._mode_obtention, 'manuel');
      input.form.onsubmit();
    }
  },

  /**
   * Affichage du datamatrix INS
   *
   * @param patient_ins
   */
  showDatamatrixIns: function (patient_id) {
    new Url('dPpatients', 'showDatamatrixIns')
      .addParam('patient_id', patient_id)
      .requestModal();
  },

  /**
   * check fields before submitting the patient search
   *
   * @param form
   * @returns {boolean|Boolean|Url}
   */
  checkSearchingFields: function (form) {
    if (!checkForm(form)) {
      return false;
    }
    return Url.update(form, 'search_result_patient');
  },

  /**
   * Modification du groupe sanguin
   *
   * @param patient_id
   */
  editGroupeSanguin: function (patient_id) {
    new Url('patients', 'editGroupeRhesus')
      .addParam("patient_id", patient_id)
      .requestModal();
  },

  /**
   * Enregistre le groupe sanguin
   *
   * @param form
   * @returns {Boolean}
   */
  saveGroupeSanguin: function (form) {
    return onSubmitFormAjax(form, function () {
      Control.Modal.close();
      if (window.TdBTamm) {
        TdBTamm.loadTdbPatient($V(form.patient_id), false);
      } else {
        Soins.reloadPatientInfosBanner($V(form.patient_id))
      }
    })
  },

  /**
   * Check if first birth name matches names
   *
   * @param display_alert
   * @param form_name
   * @returns {boolean}
   */
  checkBirthNameMatchesNames(display_alert = false, form_name = 'editFrm') {
    let match = true;

    if (Patient.ref_pays !== '1') {
      return match;
    }

    let form = getForm(form_name);
    let prenom = $V(form.prenom).toUpperCase();
    let prenoms = $V(form.prenoms).toUpperCase().split(/[' \-]/);

    if (!$V(form.prenoms) || !prenom) {
      Patient.displayWarningOnNames(match, form_name);
      return match;
    }

    let prenoms_to_check = prenom.split(/[' \-]/);
    if (prenoms_to_check.length > prenoms.length || prenoms.slice(0, prenoms_to_check.length).join() !== prenoms_to_check.join()) {
      match = false;
    }

    if (display_alert && !match) {
      alert($T('CPatient.first_birth_name_warning'));
    }

    Patient.displayWarningOnNames(match, form_name);

    return match;
  },

  /**
   * Display the warning depending on checkBirthNameMatchesNames
   *
   * @param match
   * @param form_name
   */
  displayWarningOnNames(match, form_name) {
    if (Patient.ref_pays !== '1') {
      return;
    }
    if (form_name !== 'editFrm' && form_name !== 'newNaissance') {
      return;
    }
    let warning_div = $('NamesMatchWarning');
    if (warning_div) {
      let label = $(`labelFor_`+form_name+`_prenom`);
      if (match){
        warning_div.hide();
        label.classList.remove('notNull');
        label.classList.add('notNullOK');
      } else {
        warning_div.show();
        label.classList.remove('notNullOK');
        label.classList.add('notNull');
      }
    }
  },

  /**
   * Autorise la modification des traits stricts du patient
   *
   * @param div
   */
  allowTraitStrictModification: function(div) {
    div.remove();

    let form = getForm('editFrm');
    let inputs = form.select('.trait-strict');

    inputs.invoke('writeAttribute', 'readonly', null);
    inputs.invoke('writeAttribute', 'disabled', null);

    // On force la création d'une source d'identité manuelle
    $V(form._mode_obtention, 'manuel');
    $V(form._force_new_manual_source, '1');
  },

  /*
   * Confirm action to create INSi source
   *
   * @param is_diff
   * @return {boolean}
   */
  confirmCreationSource: function (is_diff) {
    if (is_diff && !Patient.checkBirthNameMatchesNames(true, 'editFrmIdentitoVigilance')) {
        return false;
    }

    return true;
  },

  /**
   * Show or hide stricts traits while adding proof identity for patient
   *
   * @param input
   */
  toggleTraitsStricts: function(input) {
    let tbody = $('traits-stricts-area');
    tbody[$V(input) === '1' ? 'show' : 'hide']();

    if ($V(input) === '0') {
      tbody.select('input,select').each((_input) => { $V(_input, ''); });
    }
  },

  /**
   * Alert user if matricule is incomplete
   *
   * @param input
   */
  checkMatricule: (input) => {
    if (/[0-9]/.test(input.value) && /_/.test(input.value)) {
      alert($T('CPatient-error-Incomplete matricule'))
    }
  },

  /**
   * Correct patients with old status VALI
   *
   * @param page
   */
  viewOldVali: (page = 0) => {
    new Url('patients', 'vwCorrectOldVali')
      .addParam('page', page)
      .requestUpdate('correct_status');
  },

  /**
   * Set PROV status for old VALI status
   */
  updateOldVali: () => {
    new Url('patients', 'updateOldVali')
      .requestUpdate('systemMsg', Patient.viewOldVali);
  }

}, window.Patient);
