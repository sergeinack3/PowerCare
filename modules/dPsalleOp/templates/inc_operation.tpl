{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $require_check_list}}
  <table class="main layout me-margin-top-5">
    <tr>
      {{foreach from=$daily_check_lists item=check_list}}
        <td>
          <h2>{{$check_list->_ref_list_type->title}}</h2>
          {{if $check_list->_ref_list_type->description}}
            <p>{{$check_list->_ref_list_type->description}}</p>
          {{/if}}

          <div id="check_list_{{$check_list->type}}_{{$check_list->list_type_id}}">
            {{mb_include module=salleOp template=inc_edit_check_list
            check_list=$check_list
            check_item_categories=$check_list->_ref_list_type->_ref_categories
            personnel=$listValidateurs
            list_chirs=$listChirs
            list_anesths=$listAnesths}}
          </div>
        </td>
      {{/foreach}}
    </tr>
  </table>
  {{mb_return}}
{{/if}}

{{if !$selOp->_id}}
  <div class="big-info">
    Veuillez sélectionner une intervention dans la liste pour pouvoir :
    <ul>
      <li>sélectionner le personnel en salle</li>
      <li>effectuer l'horodatage</li>
      <li>coder les diagnostics</li>
      <li>coder les actes</li>
      <li>consulter le dossier</li>
    </ul>
  </div>
  {{mb_return}}
{{/if}}

{{assign var="sejour"           value=$selOp->_ref_sejour}}
{{assign var="patient"          value=$sejour->_ref_patient}}
{{assign var="module"           value="dPsalleOp"}}
{{assign var="object"           value=$selOp}}
{{assign var="do_subject_aed"   value="do_planning_aed"}}

{{assign var=vue_condensee_dossier_soins value="soins Other vue_condensee_dossier_soins"|gconf}}

{{if "dPmedicament"|module_active}}
  {{mb_script module=medicament script=medicament_selector ajax=1}}
  {{mb_script module=medicament script=equivalent_selector ajax=1}}
{{/if}}

{{if "dPprescription"|module_active}}
  {{mb_script module=prescription script=element_selector ajax=1}}
  {{mb_script module=prescription script=prescription     ajax=1}}
{{/if}}

{{mb_script module=salleOp      script=salleOp         ajax=1}}
{{mb_script module=compteRendu  script=document        ajax=1}}
{{mb_script module=compteRendu  script=modele_selector ajax=1}}
{{mb_script module=files        script=file            ajax=1}}
{{mb_script module=bloodSalvage script=bloodSalvage    ajax=1}}
{{mb_script module=soins        script=soins           ajax=1}}
{{if "planSoins"|module_active}}
  {{mb_script module=planSoins  script=plan_soins      ajax=1}}
{{/if}}
{{mb_script module=cim10        script=CIM             ajax=1}}

{{mb_include module=salleOp template=js_codage_ccam}}

{{if "monitoringPatient"|module_active && (("monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf) || ("monitoringMaternite"|module_active && "monitoringMaternite general active_graph_supervision"|gconf))}}
  {{mb_script module=monitoringPatient script=surveillance_perop ajax=1}}
  {{mb_script module=monitoringPatient script=surveillance_timeline ajax=1}}
  {{mb_script module=monitoringPatient script=surveillance_timeline_item ajax=1}}
  {{mb_script module=monitoringPatient script=supervision_graph_defaults ajax=1}}
{{/if}}

{{if "syntheseMed"|module_active}}
  {{mb_script module=syntheseMed script=vue_medecin ajax=true}}
{{/if}}

{{if "maternite"|module_active && $sejour->grossesse_id}}
  {{mb_script module=maternite script=tdb ajax=1}}
  {{mb_script module=maternite script=naissance ajax=1}}
{{/if}}

<script>
  printFicheAnesth = function (dossier_anesth_id) {
    var url = new Url("cabinet", "print_fiche");
    url.addParam("dossier_anesth_id", dossier_anesth_id);
    url.popup(700, 500, "printFiche");
  };

  submitTiming = function (oForm) {
    onSubmitFormAjax(oForm, function () {
      SalleOp.reloadTiming($V(oForm.operation_id));
    });
  };

  submitAnesth = function (oForm) {
    onSubmitFormAjax(oForm, function () {
      {{if "monitoringPatient"|module_active && "monitoringBloc"|module_active && !"monitoringBloc general active_graph_supervision"|gconf}}
      if (Prescription.updatePerop) {
        Prescription.updatePerop('{{$selOp->sejour_id}}');
      }
      {{/if}}
      reloadAnesth($V(oForm.operation_id));

      {{if "monitoringPatient"|module_active && "monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf}}
      if ($('surveillance_preop')) {
        reloadAnesth($V(oForm.operation_id), 'preop-anesth', 'preop');
      }
      if ($('surveillance_perop')) {
        reloadSurveillance.perop();
      }
      if ($('surveillance_sspi')) {
        reloadSurveillance.sspi();
      }
      {{/if}}

      if ($('partogramme-anesth')) {
        reloadAnesth($V(oForm.operation_id), 'partogramme-anesth', 'partogramme');
      }

      var formVisite = getForm("visiteAnesth");
      if (formVisite && $V(formVisite.date_visite_anesth) == 'current') {
        $V(formVisite.prat_visite_anesth_id, $V(oForm.anesth_id));
      }

      // reload timings in the timeline
      var timeline = oForm.up('.tab-container').down('.surveillance-timeline-container');
      if (timeline) {
        timeline.retrieve('timeline').updateTimings();
      }
    });
  };

  submitAnesthForm = function (oForm) {
    var form_event = getForm('editSortieSansSSPI' + $V(oForm.operation_id));

    $V(form_event.sortie_sans_sspi, $V(oForm.sortie_sans_sspi));

    onSubmitFormAjax(form_event, function () {
      {{if "monitoringPatient"|module_active && "monitoringBloc"|module_active && !"monitoringBloc general active_graph_supervision"|gconf}}
      if (Prescription.updatePerop) {
        Prescription.updatePerop('{{$selOp->sejour_id}}');
      }
      {{/if}}
      reloadAnesth($V(oForm.operation_id));

      {{if "monitoringPatient"|module_active && "monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf}}
      if ($('surveillance_preop')) {
        reloadAnesth($V(oForm.operation_id), 'preop-anesth', 'preop');
      }
      if ($('surveillance_perop')) {
        reloadSurveillance.perop();
      }
      if ($('surveillance_sspi')) {
        reloadSurveillance.sspi();
      }
      {{/if}}

      if ($('partogramme-anesth')) {
        reloadAnesth($V(oForm.operation_id), 'partogramme-anesth', 'partogramme');
      }

      var formVisite = getForm("visiteAnesth");
      if (formVisite && $V(formVisite.date_visite_anesth) == 'current') {
        $V(formVisite.prat_visite_anesth_id, $V(oForm.anesth_id));
      }

      // reload timings in the timeline
      var timeline = oForm.up('.tab-container').down('.surveillance-timeline-container');
      if (timeline) {
        timeline.retrieve('timeline').updateTimings();
      }
    });
  };

  reloadAnesth = function (operation_id, div_id, prefix_form, complete_view) {
    var url = new Url("salleOp", "httpreq_vw_anesth");
    url.addParam("operation_id", operation_id);
    url.addParam("complete_view", complete_view);

    if (div_id && prefix_form) {
      url.addParam("prefix_form", prefix_form);
    } else {
      div_id = "anesth";
    }
    url.requestUpdate(div_id, function () {
      ActesCCAM.refreshList(operation_id, "{{$selOp->chir_id}}");
    });
  };

  reloadDiagnostic = function (sejour_id) {
    var url = new Url("salleOp", "httpreq_diagnostic_principal");
    url.addParam("sejour_id", sejour_id);
    url.requestUpdate("cim");
  };

  reloadAntecedents = function () {
    var url = new Url("cabinet", "listAntecedents");
    url.addParam("sejour_id", "{{$selOp->sejour_id}}");
    url.addParam('context_date_max', '{{$sejour->sortie|date_format:'%Y-%m-%d'}}');
    url.addParam('context_date_min', '{{$sejour->entree|date_format:'%Y-%m-%d'}}');
    url.requestUpdate("antecedents");
  };

  reloadActes = function () {
    var url = new Url("salleOp", "ajax_refresh_actes");
    url.addParam("operation_id", "{{$selOp->_id}}");
    url.requestUpdate("codage_actes");
  };

  var constantesMedicalesDrawn = false;
  refreshConstantesHack = function (sejour_id) {
    (function () {
      if (constantesMedicalesDrawn == false && $('constantes-medicales').visible() && sejour_id) {
        refreshConstantesMedicales('CSejour-' + sejour_id{{if $selOp->_ref_salle && $selOp->_ref_salle->_ref_bloc && $selOp->_ref_salle->_ref_bloc->_guid}}, "{{$selOp->_ref_salle->_ref_bloc->_guid}}"{{/if}});
        constantesMedicalesDrawn = true;
      }
    }).delay(0.5);
  };

  refreshConstantesMedicales = function (context_guid, host_guid) {
    if (context_guid) {
      var url = new Url("patients", "httpreq_vw_constantes_medicales");
      url.addParam("context_guid", context_guid);
      if (host_guid) {
        url.addParam("host_guid", host_guid);
      }
      if (window.oGraphs) {
        url.addParam('hidden_graphs', JSON.stringify(window.oGraphs.getHiddenGraphs()));
      }
      url.requestUpdate("constantes-medicales");
    }
  };

  window.reloadSurveillance = {};
  ["preop", "perop", "sspi", "partogramme"].each(function (type) {
    window.reloadSurveillance[type] = function (hide_infos) {
      var container = $('surveillance_' + type);
      if (container) {

        if (window.Timeline && Timeline.timelines) {
          var t;
          while (t = Timeline.timelines.shift()) {
            t.destroy();
          }
        }

        container.update("");

        var url = new Url("salleOp", "ajax_vw_surveillance_perop");
        url.addParam("operation_id", "{{$selOp->_id}}");
        url.addParam("type", type);

        if (!Object.isUndefined(hide_infos)) {
          url.addParam("hide_infos", hide_infos);
        }

        url.requestUpdate(container, {
          onComplete: function () {
            var timeline = $('surveillance_' + type).down('.surveillance-timeline-container');
            var current_time = timeline.get('current_time');

            if (current_time) {
              setTimeout(function () {
                timeline.down('.fa-bullseye').up('button').click();
              }, 1000);
            }
          }
        });
      }
    };
  });

  confirmeCloture = function () {
    return confirm("Action irréversible. Seul le service PSMI pourra modifier le codage de vos actes. Confirmez-vous la cloture de votre codage pour aujourd'hui ?");
  };

  reloadAtcdMajeur = function () {
    var url = new Url("patients", "ajax_atcd_majeur");
    url.addParam("patient_id", "{{$patient->_id}}");
    url.requestUpdate("atcd_majeur", {
      insertion: function (element, content) {
        element.innerHTML = content;
      }
    });
  };

  changeSortieReelle = function (elt) {
    var timing_sejour = getForm('timing_use_sortie_sejour_ext');
    $V(timing_sejour.sortie_reelle, elt.value);
    return onSubmitFormAjax(timing_sejour, function () {
      SalleOp.reloadTiming($V(elt.form.operation_id));
    });
  };

  Main.add(function () {
    // Sauvegarde de l'operation_id selectionné (utile pour l'ajout de DMI dans la prescription)
    window.DMI_operation_id = "{{$selOp->_id}}";

    SalleOp.operation_id = '{{$selOp->_id}}';
    SalleOp.sejour_id = '{{$selOp->sejour_id}}';
    SalleOp.praticien_id = '{{$selOp->_praticien_id}}';
    SalleOp.operateur_ids = '{{$operateurs_disp_vasc}}';

    if (window.Prescription) {
      Prescription.hide_header = true;
    }

    // Initialisation des onglets
    window.tabs_operation = Control.Tabs.create('main_tab_group', true);
    {{if $fragment}}
    window.tabs_operation.setActiveTab('{{$fragment}}');
    {{/if}}
    var tabName = window.tabs_operation.activeContainer.id;

    switch (tabName) {
      case "disp_vasculaire":
        SalleOp.loadPosesDispVasc();
        SalleOp.reloadBloodSalvage();
        break;
      case "diag_tab":
        reloadDiagnostic('{{$selOp->sejour_id}}');
        break;
      case "codage_tab":
        reloadActes();
        break;
      case 'obs':
        Soins.loadObservations('{{$selOp->sejour_id}}');
        break;
      case "dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}":
        loadSuiviSoins();
        break;
      case "prescription_sejour_tab":
        Prescription.reloadPrescSejour('', '{{$selOp->_ref_sejour->_id}}', null, '{{$selOp->_id}}', null, null);
        break;
      case "constantes-medicales":
        constantesMedicalesDrawn = false;
        refreshConstantesHack('{{$selOp->sejour_id}}');
        break;
      case "antecedents":
        reloadAntecedents();
        break;
      case "Imeds_tab":
        SalleOp.reloadImeds();
        break;
      case "grossesse":
        Naissance.refreshGrossesse('{{$selOp->_id}}', 0);
        break;
      case "surveillance_preop":
        reloadSurveillance.preop();
        break;
      case "surveillance_perop":
        reloadSurveillance.perop();
        break;
      case "surveillance_sspi":
        reloadSurveillance.sspi();
        break;
      case "docs":
        SalleOp.loadDocuments('{{$selOp->sejour_id}}', '{{$selOp->_id}}');
        break;
      case "timing_tab":
      default:
        // Par défault, le volet timing est le premier chargé
        SalleOp.reloadTimingTab();
    }

    // Effet sur le programme
    if ($('listplages') && $('listplages-trigger')) {
      new PairEffect("listplages", {sEffect: "appear", bStartVisible: true});
    }
  });

  submitSuivi = function (oForm) {
    sejour_id = $V(oForm.sejour_id);
    onSubmitFormAjax(oForm, function () {
      Soins.loadSuivi(sejour_id);
      Control.Modal.close();
      if ($V(oForm.object_class) != "" || $V(oForm.libelle_ATC) != "") {
        // Refresh de la partie administration
        PlanSoins.loadTraitement(sejour_id, "{{$date}}", "", "administration");
        Soins.loadObservations(sejour_id);
      }
    });
  };

  loadSuiviSoins = function () {
    Soins.loadSuiviSoins('{{$selOp->sejour_id}}', '{{$date}}');
    {{if $vue_condensee_dossier_soins}}
    loadSuiviLite();
    {{/if}}
  };

  loadSuiviLite = function () {
    // Transmissions
    Soins.loadLiteSuivi('{{$sejour->_id}}');

    // Constantes
    var url = new Url("patients", "httpreq_vw_constantes_medicales_widget");
    url.addParam("context_guid", "{{$sejour->_guid}}");
    url.requestUpdate("constantes-medicales-widget");

    // Formulaires
    {{if "forms"|module_active}}
    {{unique_id var=unique_id_widget_forms}}
    ExObject.loadExObjects("{{$sejour->_class}}", "{{$sejour->_id}}", "{{$unique_id_widget_forms}}", 0.5);
    {{/if}}
  };

  reloadAtcdOp = function () {
    var url = new Url('patients', 'httpreq_vw_antecedent_allergie');
    url.addParam('sejour_id', "{{$sejour->_id}}");
    url.requestUpdate('atcd_allergies', {
      insertion: function (element, content) {
        element.innerHTML = content;
      }
    });
  };
</script>

{{mb_include module=soins template=inc_common_forms}}

<!-- Informations générales sur l'intervention et le patient -->
{{mb_include module="salleOp" template="inc_header_operation" patient=$patient}}

<!-- Tabulations -->
<ul id="main_tab_group" class="control_tabs me-align-auto me-margin-top-8 me-control-tabs-wraped">
  {{if !"dPsalleOp COperation anesth_mode"|gconf && (!$currUser->_is_praticien || $currUser->_is_praticien && $can->edit)}}
    <li onmousedown="SalleOp.reloadTimingTab()"><a href="#timing_tab">Timings</a></li>
  {{/if}}

  <li onmousedown="SalleOp.loadPosesDispVasc(); SalleOp.reloadBloodSalvage();"><a href="#disp_vasculaire">Dispositifs
      vasc.</a></li>

  {{if !"dPsalleOp COperation anesth_mode"|gconf}}
    {{if (!$currUser->_is_praticien || ($currUser->_is_praticien && $can->edit) || ($currUser->_is_praticien && $codage_prat))}}
      <li onmousedown="reloadActes()"><a href="#codage_tab">{{tr}}CCodable-actes{{/tr}} et diags.</a></li>
    {{/if}}

    {{if !$currUser->_is_praticien || ($currUser->_is_praticien && $can->edit) || ($currUser->_is_praticien && $currUser->_is_anesth)}}
      {{assign var=callback value=refreshVisite}}
      <li
        onmouseup="reloadAnesth('{{$selOp->_id}}', null, null, 1); refreshAnesthPerops('{{$selOp->_id}}'); {{if "monitoringPatient"|module_active && "monitoringBloc"|module_active && !"monitoringBloc general active_graph_supervision"|gconf && "dPprescription"|module_active}}Prescription.updatePerop('{{$selOp->sejour_id}}');{{/if}}">
        <a href="#anesth_tab">Anesth.
          {{if !$last_file_anesthesia}}
            <span class="texticon texticon-stup texticon-stroke_light"
                  title="Pas de consultation préanesthésique reliée"
                  style="{{if $selOp->_ref_consult_anesth->_id}}display:none;{{/if}}"
                  id="cpa_{{$selOp->_guid}}">CPA</span>
            <span class="texticon texticon-stup texticon-stroke_light" title="Pas de visite préanesthésique effectuée"
                  style="{{if $selOp->date_visite_anesth}}display:none;{{/if}}" id="vpa_{{$selOp->_guid}}">VPA</span>
          {{/if}}
        </a>
      </li>
    {{/if}}
    {{if !$currUser->_is_praticien || ($currUser->_is_praticien && $can->edit) || ($currUser->_is_praticien && !$currUser->_is_anesth)}}
      <li><a href="#dossier_tab">Chir.</a></li>
    {{/if}}

    {{if $app->_ref_user->isPraticien()}}
      <li onmousedown="Soins.loadObservations('{{$selOp->sejour_id}}');"><a href="#obs">{{tr}}soins.tab.obs{{/tr}}</a>
      </li>
    {{/if}}

    {{if "dPprescription"|module_active}}
      <li onmouseup="loadSuiviSoins();">
        <a href="#dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}">Suivi soins</a>
      </li>
      <li
        onmousedown="Prescription.reloadPrescSejour('', '{{$selOp->_ref_sejour->_id}}', null, '{{$selOp->_id}}', null, null);">
        <a href="#prescription_sejour_tab">Prescription</a>
      </li>
    {{/if}}
    <li onmousedown="refreshConstantesHack('{{$selOp->sejour_id}}');"><a href="#constantes-medicales">Surveillance</a>
    </li>
    <li onmousedown="SalleOp.loadDocuments('{{$selOp->sejour_id}}', '{{$selOp->_id}}')"><a
        href="#docs">{{tr}}CCompteRendu|pl{{/tr}}</a></li>
    <li onmousedown="reloadAntecedents()"><a href="#antecedents">Atcd.</a></li>
  {{/if}}

  {{if $isImedsInstalled}}
    <li onmousedown="SalleOp.reloadImeds()"><a href="#Imeds_tab">Labo</a></li>
  {{/if}}

  {{if "maternite"|module_active && $sejour->grossesse_id}}
    <li onmouseup="Naissance.refreshGrossesse('{{$selOp->_id}}', 0);">
      <a href="#grossesse">{{tr}}CAccouchement{{/tr}}</a>
    </li>
  {{elseif "monitoringPatient"|module_active && "monitoringBloc"|module_active &&
  "monitoringBloc general active_graph_supervision"|gconf}}
    {{if $selOp->debut_prepa_preop || $selOp->entree_bloc || $monitoring_sessions|@count}}
      <li onmouseup="reloadSurveillance.preop();">
        <a href="#surveillance_preop"
           {{if !$selOp->entree_salle && !$selOp->sortie_salle}}class="wrong"{{/if}}>
          {{tr}}CDailyCheckItemCategory.type.preop{{/tr}}</a>
      </li>
    {{/if}}
    <li onmouseup="reloadSurveillance.perop();">
      <a href="#surveillance_perop"
         {{if $selOp->entree_salle && !$selOp->sortie_salle}}class="wrong"{{/if}}>
        {{tr}}CProduitLivretTherapeutique-perop{{/tr}}</a>
    </li>
    {{if $selOp->entree_reveil}}
      <li onmouseup="reloadSurveillance.sspi();">
        <a href="#surveillance_sspi"
           {{if $selOp->entree_reveil && !$selOp->sortie_reveil_reel}}class="wrong"{{/if}}>
          {{tr}}CSupervisionGraph-type-sspi{{/tr}}</a>
      </li>
    {{/if}}
  {{/if}}

  <li style="float: right" class="me-tabs-buttons">
    {{if "dmi"|module_active && "dmi CDM active"|gconf}}
      {{mb_include module=dmi template=inc_button_dmi operation=$selOp}}
    {{/if}}

    {{if "vivalto"|module_active && "vivalto general url_application"|gconf && $can->edit}}
      {{mb_include module=vivalto template=inc_button_dmi operation=$selOp}}
    {{/if}}

    {{if "syntheseMed"|module_active}}
      {{mb_include module=syntheseMed template=inc_button_synthese}}
    {{/if}}

    {{me_button icon=search label="CProtocoleOperatoire|pl" onclick="SalleOp.manageProtocolesOp(null, 'protocole')"}}

    {{me_button icon=search label="CMaterielOperatoire" onclick="SalleOp.manageProtocolesOp(null, 'consommation')"}}

    {{me_dropdown_button button_icon=down button_label=CMaterielOperatoire use_anim=false button_class="me-secondary"
    container_class="me-dropdown-button-right me-float-right"}}

    {{assign var=buttons_list value=""}}
    {{me_button icon=print label="COperation-action-Block sheet" onclick="SalleOp.printFicheBloc()"}}

    {{if ("monitoringPatient"|module_active && "monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf)}}
      {{me_button icon=print label="CSupervisionGraphToPack-action-Print monitoring" onclick="SurveillancePerop.printSurveillance('`$selOp->_id`')"}}
      {{if "planSoins"|module_active}}
        {{me_button icon=print label="common-Voucher|pl" onclick="PlanSoins.printBons('`$sejour->_ref_prescription_sejour->_id`', null, 1, '`$selOp->_id`')"}}
      {{/if}}
    {{else}}
      {{if "planSoins"|module_active && "planSoins general show_bouton_plan_soins"|gconf}}
        {{me_button icon=print label="CService.plan_soins" onclick="SalleOp.printPlanSoins()"}}
      {{/if}}
    {{/if}}

    {{me_dropdown_button button_icon=down use_anim=false button_label=Print button_class="me-secondary"
    container_class="me-dropdown-button-right me-float-right"}}
  </li>
</ul>

<!-- Timings + Personnel -->
{{if !"dPsalleOp COperation anesth_mode"|gconf && (!$currUser->_is_praticien || $currUser->_is_praticien && $can->edit)}}
  <div id="timing_tab" style="display:none" class="me-align-auto">
    <div id="check_lists">
      {{mb_include module=salleOp template=inc_vw_operation_check_lists}}
    </div>
    <div id="listProtocoles">
      {{mb_include module=planningOp template=inc_vw_infos_protocoles_operatoires operation=$selOp}}
    </div>
    <div id="timing"></div>
    <div id="listPersonnel"></div>
  </div>
{{/if}}

<div id="disp_vasculaire" style="display:none" class="me-align-auto">
  <fieldset style="clear: both;">
    <legend>{{tr}}CPoseDispositifVasculaire{{/tr}}</legend>
    <div id="list-pose-dispositif-vasculaire"></div>
  </fieldset>

  {{if "bloodSalvage"|module_active && (!$currUser->_is_praticien || $currUser->_is_praticien && $can->edit)}}
    <fieldset class="me-margin-top-10">
      <legend>{{tr}}CCellSaver{{/tr}}</legend>
      <div id="bloodsalvage_form"></div>
    </fieldset>
  {{/if}}
</div>

{{if !"dPsalleOp COperation anesth_mode"|gconf}}
  {{if (!$currUser->_is_praticien || $currUser->_is_praticien && $can->edit || $currUser->_is_praticien && $codage_prat)}}
    <!-- codage des acte ccam et ngap -->
    <div id="codage_tab" style="display: none" class="me-align-auto">
      <div style="display: flex; flex-wrap: nowrap; clear: both">
        <form name="infoFactu" method="post" style="flex-grow: 1">
          {{mb_key object=$selOp}}
          {{mb_class object=$selOp}}
          <input type="hidden" name="del" value="0"/>
          <table class="form me-margin-top-0">
            <tr>
              <th>
                {{mb_label object=$selOp field=anapath onclick="SalleOp.infoExamen($(this.getAttribute('for')+'_1'));"}}
              </th>
              <td>
                {{mb_field object=$selOp field=anapath typeEnum="radio" onchange="SalleOp.infoExamen(this);"}}
                <button type="button" class="edit notext"
                        {{if !$selOp->anapath || $selOp->anapath == "?"}}style="visibility: hidden;"{{/if}}
                        title="{{tr}}COperation-_modify_anapath{{/tr}}"
                        onclick="SalleOp.infoExamen(this.form.anapath[0])"></button>
                <button type="button" class="print notext"
                        {{if !$selOp->anapath || $selOp->anapath == "?"}}style="visibility: hidden;"{{/if}}
                        title="{{tr}}Print{{/tr}}"
                        onclick="SalleOp.printBon('ANAPATH');">
                </button>
              </td>
              <th>
                {{mb_label object=$selOp field=prothese}}
              </th>
              <td>{{mb_field object=$selOp field=prothese typeEnum="radio" onchange="onSubmitFormAjax(this.form);"}}</td>
              <th>{{mb_label object=$selOp field=urgence}}</th>
              <td>{{mb_field object=$selOp field=urgence onchange="onSubmitFormAjax(this.form);"}}</td>
            </tr>
            <tr>
              <th>
                {{mb_label object=$selOp field=labo onclick="SalleOp.infoExamen($(this.getAttribute('for')+'_1'));"}}
              </th>
              <td style="vertical-align: middle;">
                {{mb_field object=$selOp field=labo typeEnum="radio" onchange="SalleOp.infoExamen(this);"}}
                <button type="button" class="edit notext"
                        {{if !$selOp->labo || $selOp->labo == "?"}}style="visibility: hidden;"{{/if}}
                        title="{{tr}}COperation-_modify_labo{{/tr}}"
                        onclick="SalleOp.infoExamen(this.form.labo[0])"></button>
                <button type="button" class="print notext"
                        {{if !$selOp->labo || $selOp->labo == "?"}}style="visibility: hidden;"{{/if}}
                        title="{{tr}}Print{{/tr}}"
                        onclick="SalleOp.printBon('BACTERIO');">
                </button>
              </td>
              <th>
                {{mb_label object=$selOp field=rayons_x onclick="SalleOp.infoExamen($(this.getAttribute('for')+'_1'));"}}
              </th>
              <td style="vertical-align: middle;">
                {{mb_field object=$selOp field=rayons_x typeEnum="radio" onchange="SalleOp.infoExamen(this);"}}
                <button type="button" class="edit notext"
                        {{if !$selOp->rayons_x || $selOp->rayons_x == "?"}}style="visibility: hidden;"{{/if}}
                        title="{{tr}}COperation-_modify_rayons_x{{/tr}}"
                        onclick="SalleOp.infoExamen(this.form.rayons_x[0])"></button>
              </td>
              <td colspan="2"></td>
            </tr>
          </table>
        </form>

        <!-- Anesthesie -->
        {{if $app->user_prefs.show_dh_salle_op}}
          <div>
            {{mb_include module=cabinet template=inc_consult_anesth/inc_depassement_anesth operation=$selOp}}
          </div>
        {{/if}}
      </div>
      <div id="codage_actes"></div>
    </div>
  {{/if}}

  <!-- Anesthesie -->
  {{if !$currUser->_is_praticien || ($currUser->_is_praticien && $can->edit) || ($currUser->_is_praticien && $currUser->_is_anesth)}}
    <div id="anesth_tab" style="display: none" class="me-align-auto">
      {{mb_include module=salleOp template=inc_vw_info_anesth}}
    </div>
  {{/if}}

  {{if !$currUser->_is_praticien || ($currUser->_is_praticien && $can->edit) || ($currUser->_is_praticien && !$currUser->_is_anesth)}}
    <!-- Documents et facteurs de risque -->
    {{assign var="dossier_medical" value=$selOp->_ref_sejour->_ref_dossier_medical}}
    <div id="dossier_tab" style="display: none" class="me-align-auto">
      <table class="tbl me-no-box-shadow">
        <tr>
          <th class="title">Facteurs de risque</th>
        </tr>
      </table>
      {{mb_include module=cabinet template=inc_consult_anesth/inc_vw_facteurs_risque sejour=$selOp->_ref_sejour patient=$selOp->_ref_sejour->_ref_patient}}
    </div>
  {{/if}}

  {{if $app->_ref_user->isPraticien()}}
    <div id="obs" style="display: none;"></div>
  {{/if}}
  <div id="docs" style="display: none;" class="me-align-auto"></div>
  <div id="constantes-medicales" style="display: none; clear: both" class="me-align-auto"></div>
  <div id="antecedents" style="display: none" class="me-align-auto"></div>
  {{if "dPprescription"|module_active}}
    <!-- Affichage de la prescription -->
    <div id="prescription_sejour_tab" style="display: none" class="me-align-auto">
      <div id="prescription_sejour"></div>
    </div>
    <!-- Affichage du dossier de soins avec les lignes "bloc" -->
    <div id="dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}" style="display: none">
      {{if $vue_condensee_dossier_soins}}
        {{mb_include module=soins template=inc_dossier_soins_widgets}}
      {{/if}}
    </div>
  {{/if}}
{{/if}}

{{if $isImedsInstalled}}
  <!-- Affichage de la prescription -->
  <div id="Imeds_tab" style="display: none" class="me-align-auto"></div>
{{/if}}

{{if "maternite"|module_active && $sejour->grossesse_id}}
  <div id="grossesse" style="display: none;" class="me-align-auto"></div>
{{elseif "monitoringPatient"|module_active && "monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf}}
  <div id="surveillance_preop" style="display: none" class="me-align-auto me-padding-2"></div>
  <div id="surveillance_perop" style="display: none" class="me-align-auto me-padding-2"></div>
  <div id="surveillance_sspi" style="display: none" class="me-align-auto"></div>
{{/if}}
