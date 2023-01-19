{{*
* @package Mediboard\Patients
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient ajax=$ajax}}
{{mb_script module=planningOp script=ccam_selector ajax=$ajax}}
{{mb_script module=patients script=pat_selector ajax=$ajax}}
{{mb_script module=cabinet script=edit_consultation ajax=$ajax}}
{{mb_script module=cim10 script=CIM ajax=$ajax}}

<script>
  changeContexteSearch = function (select) {
    if (select.value === 'prescription') {
      $$('.row-specialite, .row-atc, .row-composant, .row-indication, .row-commentaire').forEach(
        function (e) {
          e.show();
        }
      );
    }
    if (select.value === 'traitement') {
      $$('.row-specialite, .row-atc, .row-composant, .row-indication, .row-commentaire').forEach(
        function (e) {
          e.hide();
        }
      );
    }
  };

  saveCriteria = function (form) {
    var criteriaForm = getForm("formSaveCriteria");

    if (!$V(criteriaForm.search_criteria_id)) {
      var title = prompt($T('CSearchCriteria-msg-Label of the criteria to be registered'));
    }

    $V(criteriaForm.title, title);

    if (title === null) {
      return;
    }

    $V(criteriaForm.user_id, $V(form.user_id));
    $V(criteriaForm.medecin_traitant_view, $V(form._view));
    $V(criteriaForm.only_medecin_traitant, $V(form.only_medecin_traitant) === true ? 1 : 0);
    $V(criteriaForm.ald, $V(form.ald) === true ? 1 : 0);

    $$('input[type=radio]').each(function (elt) {
      if (elt.checked) {
        $V(criteriaForm.section_choose, elt.value);
      }
    });

    {{foreach from=$criteria->getProperties() item=_value key=_property}}
    {{if $_property != '_view' && $_property != '_shortview' && $_property != 'search_criteria_id'
  && $_property != 'owner_id' && $_property != 'created' && $_property != 'title'
  && $_property != 'medecin_traitant_view' && $_property != 'only_medecin_traitant' && $_property != 'user_id' && $_property != 'section_choose'}}
    {{if $_property == 'age_min' || $_property == 'age_max' || $_property == 'rques_consult' || $_property == 'examen_consult'
  || $_property == 'rques_sejour' || $_property == 'libelle_interv' || $_property == 'rques_interv' || $_property == 'pat_name'}}
    $V(criteriaForm.{{$_property}}, $V(form._{{$_property}}));
    {{else}}
    $V(criteriaForm.{{$_property}}, $V(form.{{$_property}}));
    {{/if}}
    {{/if}}
    {{/foreach}}

    {{if !$mod_tamm}}
    $V(criteriaForm.date_min, $V(form.entree));
    $V(criteriaForm.date_max, $V(form.sortie));
    {{else}}
    $V(criteriaForm.date_min, $V(form._date_min));
    $V(criteriaForm.date_max, $V(form._date_max));
    {{/if}}

    onSubmitFormAjax(criteriaForm, {
      onComplete: function () {
        Control.Modal.stack.length ? Control.Modal.refresh() : document.location.reload()
      }
    });
  };

  deleteCriteria = function () {
    var confirmation = confirm($T('CSearchCriteria-action-Do you want to delete these search criteria'));

    if (confirmation == true) {
      var criteriaForm = getForm("formSaveCriteria");
      $V(criteriaForm.del, 1);

      onSubmitFormAjax(criteriaForm, {
        onComplete: function () {
          Control.Modal.stack.length ? Control.Modal.refresh() : document.location.reload()
        }
      });
    }

  };

  showClinicalFolder = function (form) {
    var url = new Url("patients", "vw_recherche_dossier_clinique");
    url.addParam("user_id", $V(form.user_id));
    url.addParam("module_tamm", "{{$mod_tamm}}");
    url.requestUpdate("recherche_clinique");
  };

  selectCriteria = function (mod_tamm) {
    var form = getForm("rechercheDossierClinique");
    var criteriaForm = getForm("formSaveCriteria");

    // vider les champs
    $V(criteriaForm.search_criteria_id, '');
    $V(criteriaForm.title, '');
    $V(form.group_by_patient, '');

    emptyPatient();
    emptyConsult();
    emptySejour();
    emptyInterv();
    emptyProduit();
    emptyLibelleProduit();
    emptyATC();
    emptyComposant();
    emptyIndication();
    emptyCommentaire();
    emptyDossierMedical();

    var url = new Url("dPpatients", "ajax_select_criterion");
    url.addParam('search_criteria_id', $V(form.select_criteria));
    url.requestJSON(function (list_criterion) {

      if (list_criterion['search_criteria_id']) {
        var button_criteria = $('save_criteria');
        button_criteria.className = "edit";
        button_criteria.innerHTML = $T('CSearchCriteria-action-Edit search criteria');
        $('delete_criteria').show();

        $V(criteriaForm.search_criteria_id, list_criterion['search_criteria_id']);
        $V(criteriaForm.title, list_criterion['title']);

        // patient
        if (mod_tamm == 1) {
          $V(form._date_min, list_criterion['date_min']);
          $V(form._date_min_da, Date.fromDATETIME(list_criterion['date_min']).toLocaleDateTime());
          $V(form._date_max, list_criterion['date_max']);
          $V(form._date_max_da, Date.fromDATETIME(list_criterion['date_max']).toLocaleDateTime());
        } else {
          $V(form.entree, list_criterion['date_min']);
          $V(form.entree_da, Date.fromDATETIME(list_criterion['date_min']).toLocaleDateTime());
          $V(form.sortie, list_criterion['date_max']);
          $V(form.sortie_da, Date.fromDATETIME(list_criterion['date_max']).toLocaleDateTime());
        }

        $V(form.patient_id, list_criterion['patient_id']);
        $V(form._pat_name, list_criterion['pat_name']);
        $V(form.sexe, list_criterion['sexe']);
        $V(form._age_min, list_criterion['age_min']);
        $V(form._age_max, list_criterion['age_max']);
        $V(form.medecin_traitant, list_criterion['medecin_traitant']);
        $V(form._view, list_criterion['medecin_traitant_view']);
        $V(form.only_medecin_traitant, list_criterion['only_medecin_traitant'] == 1 ? true : false);
        $V(form.ald, list_criterion['ald'] == 1 ? true : false);
        $V(form.rques, list_criterion['rques']);
        $V(form.libelle_evenement, list_criterion['libelle_evenement']);
        $V(form.group_by_patient, list_criterion['group_by_patient'] == 1 ? 1 : 0);

        if (mod_tamm == 0) {
          updateSection(list_criterion['section_choose'] + "_section");
          $('rechercheDossierClinique_section_choose_' + list_criterion['section_choose']).checked = true;
        }

        // Dossier patient
        $V(form.hidden_list_antecedents_cim10, list_criterion['hidden_list_antecedents_cim10']);
        updateTokenCim10($V(form.hidden_list_antecedents_cim10), "antecedents_cim10");
        $V(form.antecedents_text, list_criterion['antecedents_text']);
        $V(form.allergie_text, list_criterion['allergie_text']);
        $V(form.hidden_list_pathologie_cim10, list_criterion['hidden_list_pathologie_cim10']);
        updateTokenCim10($V(form.hidden_list_pathologie_cim10), "pathologie_cim10");
        $V(form.pathologie_text, list_criterion['pathologie_text']);
        $V(form.hidden_list_probleme_cim10, list_criterion['hidden_list_probleme_cim10']);
        updateTokenCim10($V(form.hidden_list_probleme_cim10), "probleme_cim10");
        $V(form.probleme_text, list_criterion['probleme_text']);


        // consult
        $V(form.motif, list_criterion['motif']);
        $V(form._rques_consult, list_criterion['rques_consult']);
        $V(form._examen_consult, list_criterion['examen_consult']);
        $V(form.conclusion, list_criterion['conclusion']);

        // sejour
        $V(form.libelle, list_criterion['libelle']);
        $V(form.type, list_criterion['type']);
        $V(form._rques_sejour, list_criterion['rques_sejour']);
        $V(form.convalescence, list_criterion['convalescence']);

        // interv
        $V(form._libelle_interv, list_criterion['libelle_interv']);
        $V(form._rques_interv, list_criterion['rques_interv']);
        $V(form.examen, list_criterion['examen']);
        $V(form.materiel, list_criterion['materiel']);
        $V(form.exam_per_op, list_criterion['exam_per_op']);
        $V(form.codes_ccam, list_criterion['codes_ccam']);

        //produit
        $V(form.code_cis, list_criterion['code_cis']);
        $V(form.code_ucd, list_criterion['code_ucd']);
        $V(form.produit, list_criterion['produit']);

        $V(form.libelle_produit, list_criterion['libelle_produit']);

        $V(form.classes_atc, list_criterion['classes_atc']);
        updateTokenATC($V(form.classes_atc));

        // composant
        $V(form.composant, list_criterion['composant']);
        $V(form.keywords_composant, list_criterion['keywords_composant']);

        // indication
        $V(form.indication, list_criterion['indication']);
        $V(form.keywords_indication, list_criterion['keywords_indication']);
        $V(form.type_indication, list_criterion['type_indication']);

        $V(form.commentaire, list_criterion['commentaire']);
      } else {
        var button_criteria = $('save_criteria');
        button_criteria.className = "save";
        button_criteria.innerHTML = $T('CSearchCriteria-action-Save search criteria');

        $('delete_criteria').hide();
      }
    });
  };

  changePage = function (start) {
    $V(getForm('rechercheDossierClinique').start, start);
  };

  updateTokenATC = function (v) {
    var i, codes = v.split("|").without("");
    for (i = 0; i < codes.length; i++) {
      codes[i] += '<button class="remove notext" type="button" onclick="ATCTokenField.remove(\'' + codes[i] + '\')"></button>';
    }
    $("list_atc").update(codes.join(", "));
    $V(getForm("rechercheDossierClinique").keywords_atc, '');
  };

  {{if $mod_tamm}}
  updateSection = function (name) {
    var section = $(name);
    section.select("input", "select", " button").invoke("writeAttribute", "disabled", null);
    section.removeClassName("opacity-30");
  };
  {{else}}
  updateSection = function (name) {
    var sections = ['consult_section', 'sejour_section', 'operation_section', 'without_medical_folder'];

    sections.each(function (section_name) {
      if (section_name != name) {
        var section = $(section_name);
        if (section_name != 'without_medical_folder') {
          section.select("input", "select", " button").invoke("setAttribute", "disabled", null);
          section.addClassName("opacity-30");
        }
      }
    });

    // Si le contexte n'est pas une consultation, on masque la recherche via dossier médical
    var section = $('dossier_medical_section');
    if (name !== 'consult_section') {
      section.select('input', 'select').invoke('setAttribute', 'disabled', null);
      section.addClassName("opacity-30");
    } else {
      section.select('input', 'select').invoke('writeAttribute', 'disabled', null);
      section.removeClassName("opacity-30");
    }

    var section = $(name);
    if (name != 'without_medical_folder') {
      section.select("input", "select", " button").invoke("writeAttribute", "disabled", null);
      section.removeClassName("opacity-30");
    }
  };
  {{/if}}


  emptyPatient = function () {
    var form = getForm("rechercheDossierClinique");
    /*$V(form.date_min, '');
    $V(form.date_min_da, '');
    $V(form.date_max, '');
    $V(form.date_max_da, '');*/
    $V(form.patient_id, '');
    $V(form._pat_name, '');
    $V(form.sexe, '');
    $V(form._age_min, '');
    $V(form._age_max, '');
    $V(form.medecin_traitant, '');
    $V(form._view, '');
    $V(form.only_medecin_traitant, '');
    $V(form.rques, '');
    $V(form.libelle_evenement, '');
  };

  emptyDossierMedical = function () {
    var form = getForm("rechercheDossierClinique");
    $V(form.hidden_list_antecedents_cim10, '');
    $V(form.antecedents_cim10, '');
    $V(form.antecedents_text, '');
    $V(form.allergie_text, '');
    $V(form.hidden_list_pathologie_cim10, '');
    $V(form.pathologie_cim10, '');
    $V(form.pathologie_text, '');
    $V(form.hidden_list_probleme_cim10, '');
    $V(form.probleme_cim10, '');
    $V(form.probleme_text, '');
    updateTokenCim10($V(form.hidden_list_antecedents_cim10), "antecedents_cim10");
    updateTokenCim10($V(form.hidden_list_pathologie_cim10), "pathologie_cim10");
    updateTokenCim10($V(form.hidden_list_probleme_cim10), "probleme_cim10");
  };

  emptyConsult = function () {
    var form = getForm("rechercheDossierClinique");
    $V(form.motif, '');
    $V(form._rques_consult, '');
    $V(form._examen_consult, '');
    $V(form.conclusion, '');
  };

  emptySejour = function () {
    var form = getForm("rechercheDossierClinique");
    $V(form.libelle, '');
    $V(form.type, '');
    $V(form._rques_sejour, '');
    $V(form.convalescence, '');
  };

  emptyInterv = function () {
    var form = getForm("rechercheDossierClinique");
    $V(form._libelle_interv, '');
    $V(form._rques_interv, '');
    $V(form.examen, '');
    $V(form.materiel, '');
    $V(form.exam_per_op, '');
    $V(form.codes_ccam, '');
  };

  emptyProduit = function () {
    var form = getForm("rechercheDossierClinique");
    $V(form.code_cis, '');
    $V(form.code_ucd, '');
    $V(form.produit, '');
  };

  emptyLibelleProduit = function () {
    $V(getForm("rechercheDossierClinique").libelle_produit, '');
  };

  emptyATC = function () {
    $V(getForm('rechercheDossierClinique').classes_atc, '');
    $V(getForm("rechercheDossierClinique").keywords_atc, '');
    updateTokenATC('');
  };

  emptyComposant = function () {
    var form = getForm("rechercheDossierClinique");
    $V(form.composant, '');
    $V(form.keywords_composant, '');
  };

  emptyIndication = function () {
    var form = getForm("rechercheDossierClinique");
    $V(form.indication, '');
    $V(form.keywords_indication, '');
    $V(form.type_indication, '');
  };

  emptyCommentaire = function () {
    var form = getForm("rechercheDossierClinique");
    $V(form.commentaire, '');
  };

  exportResults = function () {
    var form = getForm("rechercheDossierClinique");
    $V(form.export, 1);
    $V(form.suppressHeaders, 1);
    form.submit();
    $V(form.export, 0);
    $V(form.suppressHeaders, 0);
  };

  onSelectPrat = function (form) {
    $V(form.function_id, "", false);
  };

  onSelectCab = function (form) {
    $V(form.user_id, "", false);
  };

  // In case you want to add some ccam searches
  var tokensCodes = [];

  Main.add(function () {
    Control.Tabs.create("tabs-prescription", true);

    var form = getForm("rechercheDossierClinique");

    {{if $mod_tamm}}
    Calendar.regField(form._date_min);
    Calendar.regField(form._date_max);
    {{else}}
    Calendar.regField(form.entree);
    Calendar.regField(form.sortie);
    {{/if}}

    // Pat Selector
    PatSelector.init = function () {
      this.sForm = "rechercheDossierClinique";
      this.sId = "patient_id";
      this.sView = "_pat_name";
      this.pop();
    };

    // Autocomplete des medicaments
    var url = new Url("dPmedicament", "httpreq_do_medicament_autocomplete");
    url.addParam("produit_max", 40);
    url.autoComplete(form.produit, "produit_auto_complete", {
      minChars:           3,
      width:              '500px',
      afterUpdateElement: function (input, selected) {
        var code_cis = selected.select(".code-cis")[0].innerHTML;
        if (code_cis != "") {
          $V(input.form.code_cis, code_cis);
        }
        // Si pas de cis, on recherche par ucd
        else {
          $V(input.form.code_ucd, selected.select(".code-ucd")[0].innerHTML);
        }
        var libelle_ucd = selected.select("small.libelle")[0].innerHTML;
        libelle_ucd = libelle_ucd.replace(/(^\s+|\s+$)/g, '').replace(/<em>|<\/em>/g, '');
        $V(input, libelle_ucd);
      }
    });

    // Autocomplete des medicaments
    var url = new Url("dPmedicament", "httpreq_do_medicament_autocomplete");
    url.addParam("produit_max", 40);
    url.autoComplete(form.produit_perso, "produit_perso_auto_complete");

    // Autocomplete et TokenField des classes ATC
    ATCTokenField = new TokenField(form.classes_atc, {
      onChange: updateTokenATC
    });

    updateTokenATC($V(form.classes_atc));

    var urlATC = new Url("medicament", "ajax_atc_autocomplete");
    urlATC.autoComplete(form.keywords_atc, null, {
      minChars:      1,
      updateElement: function (selected) {
        var form = getForm("rechercheDossierClinique");
        $V(form.keywords_atc, selected.select(".view")[0].innerHTML.replace(/<em>|<\/em>/g, ''));
        ATCTokenField.add($V(form.keywords_atc), true);
      }
    });

    // Autocomplete des composants
    var urlComposant = new Url("medicament", "ajax_composant_autocomplete");
    urlComposant.autoComplete(form.keywords_composant, null, {
      minChars:           3,
      afterUpdateElement: function (input, selected) {
        var form = getForm("rechercheDossierClinique");
        $V(input, selected.select(".view")[0].innerHTML.replace(/<em>|<\/em>/g, ''));
        $V(form.composant, selected.get("code"));
      }
    });

    // Autocomplete des indications
    var urlIndication = new Url("medicament", "ajax_indication_autocomplete");
    urlIndication.autoComplete(form.keywords_indication, null, {
      minChars:           3,
      afterUpdateElement: function (input, selected) {
        var form = getForm("rechercheDossierClinique");
        $V(input, selected.select(".view")[0].innerHTML.replace(/<em>|<\/em>/g, ''));
        $V(form.indication, selected.get("code"));
        $V(form.type_indication, selected.get("type"));
      }
    });

    updateSection("consult_section");


    // CIM search
    var cim_searches = ['antecedents_cim10', 'pathologie_cim10', 'probleme_cim10'];
    var token_fields = [];
    updateTokenCim10 = function (value, input_name) {
      var codes = value.split('|').without('');
      for (var i = 0; i < codes.length; i++) {
        codes[i] += '<button class="remove notext" type="button" ' +
          'onclick="removeCim(\'' + codes[i] + '\', \'' + input_name + '\')"></button>'
      }
      if (input_name === 'pathologie_cim10' || input_name === 'probleme_cim10') {
        $('list_' + input_name).update(codes.join(' ou '));
      } else {
        $('list_' + input_name).update(codes.join(', '));
      }
    };

    removeCim = function (value, input) {
      token_fields[input].remove(value);
    };

    cim_searches.forEach(function (input_name) {
      var cim_search = getForm("rechercheDossierClinique")[input_name];

      token_fields[input_name] = new TokenField(getForm("rechercheDossierClinique")['hidden_list_' + input_name], {
        onChange: function (v) {
          updateTokenCim10(v, input_name)
        }
      });

      CIM.autocomplete(cim_search, null, {
        afterUpdateElement: function () {
          token_fields[input_name].add($V(cim_search));
          getForm('rechercheDossierClinique')[input_name].value = '';
        }
      });
    });

    $$('input[value="sejour"], input[value="operation"]').forEach(function (e) {
      e.on('change', function (r) {
        $$('.row-specialite, .row-atc, .row-composant, .row-indication, .row-commentaire').forEach(
          function (e) {
            e.show();
          }
        );
        $('rechercheDossierClinique_contexte_recherche').value = 'prescription';
        // Désactivation de l'option Traitement
        $('rechercheDossierClinique_contexte_recherche').namedItem('traitement').setAttribute('disabled', true);
      });
    });
    $$('input[value="consult"]').forEach(function (e) {
      e.on('change', function (r) {
        // Activation du l'option Traitement
        $('rechercheDossierClinique_contexte_recherche').namedItem('traitement').removeAttribute('disabled');
      });
    });

    if ($V(getForm('rechercheDossierClinique').annule) === '0') {
      $('select_motif_annulation').hide();
      $V($$('#select_motif_annulation select[name=motif_annulation]'), null);
    }


    var oForm = getForm("rechercheDossierClinique");


    removeTokenCCAM = function (value, section) {
      tokensCodes[section].remove(value);
    };

    updateTokens = function (value, section, separator = ' ') {
      var codes = value.split('|').without('');

      for (var i = 0; i < codes.length; i++) {
        codes[i] += '<button class="remove notext" type="button" ' +
          'onclick="removeTokenCCAM(\'' + codes[i] + '\', \'' + section + '\')"></button>'
      }

      oForm['hidden_' + section] = value;
      $('token_list_' + section).update(codes.join(separator));
    };


    tokensCodes['codes_ccam_consult'] = new TokenField(oForm.hidden_codes_ccam_consult, {
      onChange: function (value) {
        updateTokens(value, 'codes_ccam_consult');
      }
    });
    tokensCodes['codes_ngap_consult'] = new TokenField(oForm.hidden_codes_ngap_consult, {
      onChange: function (value) {
        updateTokens(value, 'codes_ngap_consult');
      }
    });

    new Url("ccam", "autocompleteCcamCodes")
      .addParam('input_field', '_codes_ccam')
      .autoComplete(oForm._codes_ccam, '', {
        minChars:      1,
        dropdown:      true,
        width:         "250px",
        updateElement: function (selected) {
          tokensCodes['codes_ccam_consult'].add(selected.down("strong").getText());
        }
      });

    new Url("cabinet", "ajax_ngap_autocomplete")
      .addParam('input_field', 'code_ngap')
      .autoComplete(oForm.code_ngap, '', {
        minChars:      1,
        dropdown:      true,
        width:         "250px",
        updateElement: function (selected) {
          tokensCodes['codes_ngap_consult'].add(selected.down(".code").getText(), true);
        }
      });


    // Default init. Changed if the consult or the intervention are switched
    CCAMSelector.init = function () {
      this.sForm = "rechercheDossierClinique";
      this.sClass = "object_class";
      this.sChir = "user_id";
      this.sView = "codes_ccam_consult";
      this.pop();
    };

    $$('input[name="section_choose"]').forEach(function (e) {
      e.observe('click', function (el) {
        if (el.target.value === 'consult') {
          $V(oForm.object_class, 'CConsultation');
          CCAMSelector.init = function () {
            this.sForm = "rechercheDossierClinique";
            this.sClass = "object_class";
            this.sChir = "user_id";
            this.sView = "codes_ccam_consult";
            this.pop();
          };
        }
        if (el.target.value === 'operation') {
          $V(oForm.object_class, 'COperation');
          CCAMSelector.init = function () {
            this.sForm = "rechercheDossierClinique";
            this.sClass = "object_class";
            this.sChir = "user_id";
            this.sView = "codes_ccam_interv";
            this.pop();
          };
        }
      });
    });


  });
</script>

<style type="text/css">
  @media print {
    #search-results {
      width: 100%;
      height: auto;
    }
  }
</style>

<div id="recherche_clinique">
  <form name="formSaveCriteria" method="post">
    {{mb_key   object=$criteria}}
    {{mb_class object=$criteria}}
    <input type="hidden" name="del" value="0" />

    {{foreach from=$criteria->getProperties() item=_value key=_property}}
      {{if $_property != '_view' && $_property != '_shortview' && $_property != 'search_criteria_id'
      && $_property != 'created'}}
        <input type="hidden" name="{{$_property}}" value="{{$_value}}" />
      {{/if}}
    {{/foreach}}
  </form>

  <form name="rechercheDossierClinique" method="get" target="_blank"
        onsubmit="if (this.user_id.value === '' && this.function_id.value === '') { Modal.alert($T('oxCabinet-No practitioner nor function')); return false; } if (getForm('rechercheDossierClinique').start.value != '0') {Control.Modal.close();}
          var url = Url.update(this, null, {openModal: true}); modal_results = url.modalObject; return false;">
    <input type="hidden" name="m" value="patients" />
    <input type="hidden" name="a" value="ajax_recherche_dossier_clinique" />
    <input type="hidden" name="start" value="0" onchange="this.form.onsubmit()" />
    <input type="hidden" name="export" value="0" />
    <input type="hidden" name="suppressHeaders" value="0" />
    <input type="hidden" name="mod_tamm" value="{{$mod_tamm}}" />
    <input type="hidden" name="object_class" value="CConsultation">

    <table class="main layout">
      <tr>
        <td colspan="2">

          <table class="main form me-no-box-shadow me-no-bg">
            {{if $list_criteria|@count > 0}}
              <tr>
                {{me_form_field field_class="me-label-bg-light" nb_cells=2 label="CSearchCriteria-Choice of criteria-desc" title_label="CSearchCriteria-Choice of criteria"}}
                  <select name="select_criteria" onchange="selectCriteria('{{$mod_tamm}}');">
                    <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                    {{foreach from=$list_criteria item=_criterion}}
                      <option value="{{$_criterion->_id}}">
                        {{$_criterion->title}}
                      </option>
                    {{/foreach}}
                  </select>
                {{/me_form_field}}
              </tr>
            {{/if}}
            <tr>
              {{me_form_field field_class="me-label-bg-light" nb_cells=2 label="selection_du_praticien"}}
                {{if $users_list|@count}}
                  <select name="user_id" onchange="onSelectPrat(this.form);showClinicalFolder(this.form);">
                    <option name="select_prat" value="">{{tr}}CMediusers-select-praticien{{/tr}}</option>
                    {{mb_include module=mediusers template=inc_options_mediuser list=$users_list selected=$user_id}}
                  </select>
                {{else}}
                  <input type="hidden" name="user_id" value="{{$app->_ref_user->_id}}" />
                  <div class="me-field-content">
                    {{$app->_ref_user|trim}}
                  </div>
                {{/if}}
              {{/me_form_field}}

              {{if $functions_list|@count}}
                {{me_form_field field_class="me-label-bg-light" nb_cells=2 label="selection_du_cabinet"}}
                  <select name="function_id" onchange="{{if $users_list|@count}}onSelectCab(this.form);{{/if}}">
                    <option name="select_function" value="">{{tr}}CFunctions-select{{/tr}}</option>
                    {{mb_include module=mediusers template=inc_options_function list=$functions_list selected=$function_id}}
                  </select>
                {{/me_form_field}}
              {{/if}}
            </tr>

            <tr {{if $mod_tamm}}style="display: none"{{/if}}>
              {{me_form_field field_class="me-label-bg-light" nb_cells=2 label="common-Start"}}
                {{mb_field object=$sejour field=entree register=true form="rechercheDossierClinique"}}
              {{/me_form_field}}
            </tr>
            <tr {{if $mod_tamm}}style="display: none"{{/if}}>
              {{me_form_field field_class="me-label-bg-light" nb_cells=2 label="end"}}
                {{mb_field object=$sejour field=sortie register=true form="rechercheDossierClinique"}}
              {{/me_form_field}}
            </tr>

            <tr {{if !$mod_tamm}}style="display: none"{{/if}}>
              {{me_form_field field_class="me-label-bg-light" nb_cells=2 label="CSearchCriteria-date_min"}}
                {{mb_field object=$sejour field=_date_min register=true form="rechercheDossierClinique"}}
              {{/me_form_field}}
            </tr>
            <tr {{if !$mod_tamm}}style="display: none"{{/if}}>
              {{me_form_field field_class="me-label-bg-light" nb_cells=2 label="CSearchCriteria-date_max"}}
                {{mb_field object=$sejour field=_date_max register=true form="rechercheDossierClinique"}}
              {{/me_form_field}}
            </tr>
            <tr>
              <td>
                  {{mb_field object=$criteria field="group_by_patient" typeEnum=checkbox onchange="\$V(this.form.__group_by_patient, \$V(this)?true:false);"}}
                  {{mb_label object=$criteria field="group_by_patient" typeEnum=checkbox}}
              </td>
            </tr>
            <tr>
              <th colspan="4" class="title me-text-align-center me-padding-8 me-no-bg me-no-title">{{tr}}CPatient{{/tr}}</th>
            </tr>

            <tr>
              <table class="main me-no-align">
                <tr class="me-row-valign">

                  <!-- 1st half Patient pane -->
                  <td class="halfPane">
                    <fieldset class="">
                      <legend>{{tr}}administratif{{/tr}}</legend>
                      <table class="form me-no-box-shadow">
                        <tr>
                          {{me_form_field nb_cells=2 mb_class=CConsultation mb_field="patient_id"}}
                            {{mb_field object=$patient field="patient_id" hidden=1 ondblclick="PatSelector.init()"}}
                            <input type="text" name="_pat_name" style="width: 15em;" readonly="readonly"
                                   onfocus="PatSelector.init()" />
                            <button class="search notext me-tertiary" type="button" onclick="PatSelector.init()">{{tr}}Search{{/tr}}</button>
                            <button class="cancel notext me-tertiary me-dark" type="button"
                                    onclick="$V(this.form.patient_id, ''); $V(this.form._pat_name, '')">{{tr}}Delete{{/tr}}</button>
                          {{/me_form_field}}

                          {{me_form_bool nb_cells=2 mb_object=$patient mb_field=ald}}
                            {{mb_field object=$patient field=ald}}
                          {{/me_form_bool}}
                        </tr>

                        <tr>
                          {{me_form_field nb_cells=2 mb_object=$patient mb_field="sexe"}}
                            {{mb_field object=$patient field=sexe emptyLabel="All"}}
                          {{/me_form_field}}
                        </tr>

                        <tr>
                          {{me_form_field field_class="me-padding-0 me-padding-left-8" layout=true nb_cells=2 mb_object=$patient mb_field=_age}}
                            entre
                            {{mb_field object=$patient field=_age_min increment=true form=rechercheDossierClinique size=2}}
                            et
                            {{mb_field object=$patient field=_age_max increment=true form=rechercheDossierClinique size=2}}
                            ans
                          {{/me_form_field}}
                        </tr>

                        <tr>
                          {{me_form_field layout=true field_class="me-padding-0 me-no-border" nb_cells=2 label="CMedecin-corresponding doctor"}}
                            <script type="text/javascript">
                              Main.add(function () {
                                var formTraitant = getForm("rechercheDossierClinique");
                                var urlTraitant = new Url("dPpatients", "httpreq_do_medecins_autocomplete");
                                urlTraitant.autoComplete(formTraitant._view, null, {
                                  minChars:      2,
                                  updateElement: function (element) {
                                    $V(formTraitant.medecin_traitant, element.id.split('-')[1]);
                                    $V(formTraitant._view, element.down(".view").innerHTML.stripTags());
                                  }
                                });
                              });
                            </script>
                            <input type="text" name="_view" value="{{$patient->_ref_medecin_traitant}}" size="25" />
                            {{mb_field object=$patient field=medecin_traitant hidden=true}}
                            <button type="button" class="cancel notext me-tertiary me-dark"
                                    onclick="this.form.medecin_traitant.value='';this.form._view.value='';"></button>
                            <br />
                            <label><input type="checkbox" name="only_medecin_traitant" /> Seulement en tant que médecin
                              traitant</label>
                          {{/me_form_field}}
                        </tr>
                      </table>
                    </fieldset>
                  </td>
                  <!-- End 1st half pane -->


                  <!-- 2nd half Patient pane -->
                  <td id="dossier_medical_section" class="halfPane">
                    <fieldset>
                      <legend>{{tr}}CDossierMedical{{/tr}}</legend>
                      <table class="form me-no-box-shadow">

                        <tr>
                          {{me_form_field field_class="me-padding-0 me-no-border" layout=true nb_cells=2 mb_object=$antecedent mb_field=rques}}
                            <input type="hidden" name="hidden_list_antecedents_cim10">
                            <input type="text" name="antecedents_cim10" value="" class="str styled-element" size="25" maxlength="255"
                                   autocomplete="off" placeholder="CIM10" onclick="this.value=''" /><br />
                            <div id="list_antecedents_cim10"></div>
                            <input type="text" name="antecedents_text" value="" class="str styled-element" size="30" maxlength="255"
                                   autocomplete="off" placeholder="{{tr}}common-Free text{{/tr}}" />
                          {{/me_form_field}}

                          {{me_form_field nb_cells=2 label="Allergie" style_css="vertical-align: top;"}}
                            <input type="text" name="allergie_text" value="" class="str style-element" size="30" maxlength="255"
                                   autocomplete="off" placeholder="{{tr}}common-Free text{{/tr}}" />
                          {{/me_form_field}}
                        </tr>

                        <tr>
                          {{me_form_field field_class="me-padding-0 me-no-border" layout=true nb_cells=2 label="CPathologie"}}
                            <input type="hidden" name="hidden_list_pathologie_cim10">
                            <input type="text" name="pathologie_cim10" value="" class="str style-element" size="25" maxlength="255"
                                   autocomplete="off" placeholder="CIM10" onclick="this.value=''" /> <br />
                            <div id="list_pathologie_cim10"></div>
                            <input type="text" name="pathologie_text" value="" class="str style-element" size="30" maxlength="255"
                                   autocomplete="off" placeholder="{{tr}}common-Free text{{/tr}}" />
                          {{/me_form_field}}

                          {{me_form_field field_class="me-padding-0 me-no-border" layout=true nb_cells=2 label="Probleme"}}
                            <input type="hidden" name="hidden_list_probleme_cim10">
                            <input type="text" name="probleme_cim10" value="" class="str style-element" size="25" maxlength="255"
                                   autocomplete="off" placeholder="CIM10" onclick="this.value=''" /> <br />
                            <div id="list_probleme_cim10"></div>
                            <input type="text" name="probleme_text" value="" class="str style-element" size="30" maxlength="255"
                                   autocomplete="off" placeholder="{{tr}}common-Free text{{/tr}}" />
                          {{/me_form_field}}
                        </tr>
                      </table>
                    </fieldset>
                  </td>
                  <!-- End 2nd half pane -->
                </tr>
              </table>
            </tr>
            <!-- End Patient structure -->

            <tr>
              <td>
                <table class="form me-margin-bottom-0">
                  <tr>
                    <th colspan="2" class="title">
                      <input type="radio" name="section_choose" style="float: left;" value="without_medical_folder"
                             onclick="updateSection('without_medical_folder')" /> {{tr}}CPatient-Without updating the medical file{{/tr}}
                    </th>
                  </tr>
                </table>
              </td>
            </tr>

            <tr>
              <td style="width: 50%">
                <table class="form">
                  <tr>
                    <th colspan="2" class="title">
                      <input type="radio" name="section_choose" value="consult"
                             style="float: left;" checked onclick="updateSection('consult_section')" /> {{tr}}CConsultation{{/tr}}
                    </th>
                  </tr>
                  <tbody id="consult_section">
                  <tr>
                    {{me_form_field nb_cells=2 mb_object=$consult mb_field=motif}}
                      {{mb_field object=$consult field=motif prop=str}}
                    {{/me_form_field}}
                  </tr>

                  <!-- champ inexistant dans la class COperation (libelle = meme nom que le champ dans CSejour) -->
                  <tr>
                    {{me_form_field nb_cells=2 label="CConsultation-rques"}}
                      <input type="text" name="_rques_consult" value="{{$consult->_rques_consult}}" size="25" />
                    {{/me_form_field}}
                  </tr>

                  <!-- champ inexistant dans la class COperation (rques = meme nom que le champ dans CSejour) -->
                  <tr class="data_to_hide">
                    {{me_form_field nb_cells=2 label="CConsultation-examen"}}
                      <input type="text" name="_examen_consult" value="{{$consult->_examen_consult}}" size="25" />
                    {{/me_form_field}}
                  </tr>

                  <tr>
                    {{me_form_field nb_cells=2 mb_object=$consult mb_field=conclusion}}
                      {{mb_field object=$consult field=conclusion prop=str}}
                    {{/me_form_field}}
                  </tr>
                  {{if "oxCabinet"|module_active}}
                    <tr id="id_select_type_consultation">
                        {{me_form_field nb_cells=2 mb_object=$consult mb_field=type_consultation}}
                        {{mb_field object=$consult field=type_consultation emptyLabel="All" value="consultation" onchange="Consultation.updateSectionConsultationInSearch(this.value)"}}
                        {{/me_form_field}}
                    </tr>
                  {{/if}}
                  <tr>
                    {{me_form_bool nb_cells=2 label="Search-cancel-consult"}}
                      <input type="checkbox" name="__annule" onchange="Consultation.searchCancelledConsult(this.checked)">
                    {{/me_form_bool}}
                  </tr>

                  <tr class="data_to_hide">
                    {{me_form_field layout=true field_class="me-padding-0 me-no-border" nb_cells=2 mb_object=$consult mb_field="codes_ccam"}}
                      {{* Can't use a mb_field*}}
                      <input id="codes_ccam_consult" type="text" size="10" name="_codes_ccam" value="" />
                      <input type="hidden" name="hidden_codes_ccam_consult" value="" />

                      <button class="add notext me-secondary" type="button"
                              onclick="tokensCodes['codes_ccam_consult'].add($V(this.form._codes_ccam), true)" title="Ajouter">Ajouter
                      </button>
                      <button class="search notext me-tertiary" type="button" onclick="CCAMSelector.init()">Rechercher</button>
                      <div id="token_list_codes_ccam_consult"></div>
                    {{/me_form_field}}
                  </tr>

                  <tr class="data_to_hide">
                    {{me_form_field layout=true nb_cells=2 field_class="me-padding-0 me-no-border" label="mod-dPccam-tab-vw_ngap"}}
                      <input type="text" name="code_ngap" value="" autocomplete="off" onclick="$V(this, '')">
                      <input type="hidden" name="hidden_codes_ngap_consult" value="" />
                      <div id="token_list_codes_ngap_consult"></div>
                    {{/me_form_field}}
                  </tr>

                  <tr id="select_motif_annulation" style="display: none">
                    {{me_form_field nb_cells=2 mb_object=$consult mb_field=motif_annulation}}
                      {{mb_field object=$consult field=motif_annulation typeEnum='select' emptyLabel='CConsultation-choose-cancellation-reason'}}
                    {{/me_form_field}}
                  </tr>
                  </tbody>
                  {{if !$mod_tamm}}
                    <tr>
                      <th colspan="2" class="title">
                        <input type="radio" name="section_choose" style="float: left;" value="sejour"
                               onclick="updateSection('sejour_section')" /> {{tr}}CSejour{{/tr}}
                      </th>
                    </tr>
                    <tbody id="sejour_section">
                    <tr>
                      {{me_form_field nb_cells=2 mb_object=$sejour mb_field=libelle}}
                        {{mb_field object=$sejour field=libelle prop=str}}
                      {{/me_form_field}}
                    </tr>

                    <tr>
                      {{me_form_field nb_cells=2 mb_object=$sejour mb_field=type}}
                        {{mb_field object=$sejour field=type emptyLabel="Tous" canNull=true}}
                      {{/me_form_field}}
                    </tr>

                    <!-- champ inexistant dans la class CSejour (rques = meme nom que le champ dans CAntecedent) -->
                    <tr>
                      {{me_form_field nb_cells=2 mb_object=$sejour mb_field=type}}
                        <input type="text" name="_rques_sejour" value="{{$sejour->_rques_sejour}}" />
                      {{/me_form_field}}
                    </tr>

                    <tr>
                      {{me_form_field nb_cells=2 mb_object=$sejour mb_field=convalescence}}
                        {{mb_field object=$sejour field=convalescence prop=str}}
                      {{/me_form_field}}
                    </tr>
                    </tbody>
                    <tr>
                      <th colspan="2" class="title">
                        <input type="radio" name="section_choose" style="float: left;" value="operation"
                               onclick="updateSection('operation_section')" /> {{tr}}COperation{{/tr}}
                      </th>
                    </tr>
                    <tbody id="operation_section">
                    <!-- champ inexistant dans la class COperation (libelle = meme nom que le champ dans CSejour) -->
                    <tr>
                      {{me_form_field nb_cells=2 label="COperation-libelle"}}
                        <input type="text" name="_libelle_interv" value="{{$interv->_libelle_interv}}" />
                      {{/me_form_field}}
                    </tr>

                    <!-- champ inexistant dans la class COperation (rques = meme nom que le champ dans CSejour) -->
                    <tr>
                      {{me_form_field nb_cells=2 label="COperation-rques"}}
                        <input type="text" name="_rques_interv" value="{{$interv->_rques_interv}}" />
                      {{/me_form_field}}
                    </tr>

                    <tr>
                      {{me_form_field nb_cells=2 mb_object=$interv mb_field=examen}}
                        {{mb_field object=$interv field=examen prop=str}}
                      {{/me_form_field}}
                    </tr>

                    <tr>
                      {{me_form_field nb_cells=2 mb_object=$interv mb_field="materiel"}}
                        {{mb_field object=$interv field=materiel prop=str}}
                      {{/me_form_field}}
                    </tr>

                    <tr>
                      {{me_form_field nb_cells=2 mb_object=$interv mb_field="exam_per_op"}}
                        {{mb_field object=$interv field=exam_per_op prop=str}}
                      {{/me_form_field}}
                    </tr>

                    <tr>
                      {{me_form_field nb_cells=2 mb_object=$interv mb_field=codes_ccam}}
                        <input name="codes_ccam_interv" size="12">
                        <button class="search notext" type="button" onclick="CCAMSelector.init()">Rechercher</button>
                        <br />
                      {{/me_form_field}}
                    </tr>
                    <tr>
                      <th class="me-no-display"></th>
                      <td>(codes complets ou partiels séparés par des virgules)</td>
                    </tr>
                    </tbody>
                  {{/if}}
                </table>
              </td>
              <td style="width: 50%; vertical-align: top;">
                {{if isset($line_med|smarty:nodefaults)}}
                  <table class="form">
                    <tr>
                      <th colspan="2" class="title">
                        <select name="contexte_recherche" onchange="changeContexteSearch(this)">
                          <option name="prescription" value="prescription">{{tr}}CPrescription{{/tr}}</option>
                          <option name="traitement" value="traitement">{{tr}}CTraitement{{/tr}}</option>
                        </select>
                      </th>
                    </tr>

                    <tr>
                      <th class="category me-text-align-left" colspan="2">
                        Produit
                      </th>
                    </tr>
                    <tr>
                      <td colspan="2">
                        <input type="hidden" name="code_cis" value="{{$line_med->code_cis}}" />
                        <input type="hidden" name="code_ucd" value="{{$line_med->code_ucd}}" />
                        <input type="text" name="produit"
                               value="{{$line_med->_ucd_view}}"
                               placeholder="&mdash; {{tr}}CPrescription.select_produit{{/tr}}" size="20"
                               style="font-weight: bold; font-size: 1.3em; width: 300px;" class="autocomplete"
                               onclick="emptyProduit(); emptyLibelleProduit(); emptyATC(); emptyComposant(); emptyIndication(); emptyCommentaire();" />
                        <div style="display:none; width: 350px;" class="autocomplete" id="produit_auto_complete"></div>
                      </td>
                    </tr>

                    <tr class="row-specialite">
                      <th class="category me-text-align-left" colspan="2">
                        Libellé de spécialité
                      </th>
                    </tr>
                    <tr class="row-specialite">
                      <td colspan="2">
                        <input type="text" name="libelle_produit"
                               value="{{$libelle_produit}}"
                               style="font-weight: bold; font-size: 1.3em; width: 317px;"
                               onclick="emptyProduit(); emptyATC(); emptyComposant(); emptyIndication(); emptyCommentaire();" />
                      </td>
                    </tr>

                    <tr class="row-atc">
                      <th class="category me-text-align-left" colspan="2">Classes ATC</th>
                    </tr>
                    <tr class="row-atc">
                      <td colspan="2">
                        <input type="hidden" name="classes_atc" value="{{$classes_atc}}" />
                        <input type="text" name="keywords_atc" class="autocomplete" value="{{$keywords_atc}}"
                               style="font-weight: bold; font-size: 1.3em; width: 300px;"
                               onclick="emptyProduit(); emptyLibelleProduit(); emptyComposant(); emptyIndication(); emptyCommentaire();" />
                        <div id="list_atc"></div>
                      </td>
                    </tr>
                    <tr class="row-composant">
                      <th class="category me-text-align-left" colspan="2">
                        Composant
                      </th>
                    </tr>
                    <tr class="row-composant">
                      <td colspan="2">
                        <input type="hidden" name="composant" value="{{$composant}}" />
                        <input type="text" name="keywords_composant" class="autocomplete"
                               onclick="emptyProduit(); emptyLibelleProduit(); emptyATC(); emptyComposant(); emptyIndication(); emptyCommentaire();"
                               style="font-weight: bold; font-size: 1.3em; width: 300px;" value="{{$keywords_composant}}" />
                      </td>
                    </tr>
                    <tr class="row-indication">
                      <th class="category me-text-align-left" colspan="2">Indication</th>
                    </tr>
                    <tr class="row-indication">
                      <td colspan="2">
                        <input type="hidden" name="indication" value="{{$indication}}" />
                        <input type="hidden" name="type_indication" value="{{$type_indication}}" />
                        <input type="text" name="keywords_indication" class="autocomplete"
                               onclick="emptyProduit(); emptyLibelleProduit(); emptyATC(); emptyComposant(); emptyIndication(); emptyCommentaire();"
                               style="font-weight: bold; font-size: 1.3em; width: 300px;" value="{{$keywords_indication}}" />
                      </td>
                    </tr>
                    <tr class="row-commentaire">
                      <th class="category me-text-align-left" colspan="2">Commentaire</th>
                    </tr>
                    <tr class="row-commentaire">
                      <td colspan="2">
                        <input type="text" name="commentaire" value="{{$commentaire}}"
                               onclick="emptyProduit(); emptyLibelleProduit(); emptyATC(); emptyComposant(); emptyIndication();"
                               style="font-size: 1.3em; width: 317px;" />
                      </td>
                    </tr>
                  </table>
                {{/if}}
              </td>
            </tr>
            <tr>
              <td colspan="2" class="button">
                <button type="button" class="search me-primary" id="search_button"
                        onclick="this.form.start.value=0; this.form.onsubmit()">
                  {{tr}}Search{{/tr}}
                </button>

                <button type="button" class="save me-secondary" id="save_criteria"
                        title="{{tr}}CSearchCriteria-action-Save search criteria-desc{{/tr}}"
                        onclick="saveCriteria(this.form);">
                  {{tr}}CSearchCriteria-action-Save search criteria{{/tr}}
                </button>

                <button class="trash me-tertiary" id="delete_criteria" onclick="deleteCriteria();" type="button" style="display: none;">
                  {{tr}}CSearchCriteria-action-Delete search criteria{{/tr}}
                </button>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </form>
</div>
