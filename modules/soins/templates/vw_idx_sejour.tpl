{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=file_category}}
{{mb_script module=files script=file}}
{{mb_script module=system script=alert}}
{{if "oxLaboClient"|module_active && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
  {{mb_script module=oxLaboClient script=oxlaboalert ajax=true}}
  {{mb_script module=oxLaboClient script=oxlaboclient ajax=true}}
{{/if}}

{{assign var=suffixe_icons value=""}}
{{if $conf.dPhospi.CLit.alt_icons_sortants}}
  {{assign var=suffixe_icons value="2"}}
{{/if}}

{{if $isImedsInstalled}}
  {{mb_script module=Imeds script=Imeds_results_watcher}}
{{/if}}
{{mb_script module=soins script=soins}}

{{if "appFineClient"|module_active}}
  {{mb_script module=appFineClient script=appFineClient ajax=true}}
{{/if}}

<script>
  function popEtatSejour(sejour_id) {
    var url = new Url("hospi", "vw_parcours");
    url.addParam("sejour_id",sejour_id);
    url.pop(1000, 700, $T('CSejour-_etat'));
  }

  function addSejourIdToSession(sejour_id) {
    var url = new Url("system", "httpreq_set_value_to_session");
    url.addParam("module","{{$m}}");
    url.addParam("name","sejour_id");
    url.addParam("value",sejour_id);
    url.requestUpdate("systemMsg");
  }

  function loadViewSejour(sejour_id, date, elt, tab) {
    var url = new Url('soins', 'viewDossierSejour');
    url.addParam('sejour_id', sejour_id);
    url.addParam('date', date);

    if (tab) {
      url.addParam('default_tab', tab);
    }
    {{if !"soins dossier_soins tab_prescription_med"|gconf}}
    else {
      var selected_tab = $$('ul#tab-sejour li a.active');
      if (selected_tab.length == 1) {
        url.addParam('default_tab', selected_tab[0].href.split("#")[1]);
      }
    }
    {{/if}}

    url.requestUpdate('dossier_sejour', function() {
      addSejourIdToSession(sejour_id);
      markAsSelected(elt);
    });
  }

  function printPatient(patient_id) {
    var url = new Url("patients", "print_patient");
    url.addParam("patient_id", patient_id);
    url.popup(700, 550, "Patient");
  }

  function updatePatientsListHeight() {
    var vpd = document.viewport.getDimensions(),
      scroller = $("left-column").down(".scroller"),
      pos = scroller.cumulativeOffset(),
      height = (vpd.height - pos[1] - 6);
    scroller.setStyle({height: height - 105  + 'px'});
  }

  function compteurAlerte(level, prescription_guid) {
    var url = new Url("prescription", "ajax_count_alerte", "raw");
    url.addParam("prescription_guid", prescription_guid);
    url.requestJSON(function(count) {
      var span_ampoule = $('span-icon-alert-'+level+'-'+prescription_guid);
      if (count[level]) {
        span_ampoule.down('span.countertip').innerHTML = count[level];
      }
      else {
        span_ampoule.down('span').remove();
        span_ampoule.down('img').remove();
      }
    });
  }

  function seeVisitesPrat() {
    var url = new Url("soins", "vw_visites_praticien");
    url.addParam("sejours_id[]", {{$visites.all|@json}});
    url.requestModal(600, 400);
  }

  Main.add(function() {
    Calendar.regField(getForm("changeDate").date, null, {noView: true});

    updatePatientsListHeight();

    Event.observe(window, "resize", updatePatientsListHeight);

    {{if $isImedsInstalled}}
      ImedsResultsWatcher.loadResults();
    {{/if}}

    {{if $app->user_prefs.show_file_view}}
      FilesCategory.showUnreadFiles();
    {{/if}}

    {{if "oxLaboClient"|module_active && $labo_alert_by_nda|@count && "oxLaboClient alert_result_critical modal_alert_result_critical"|gconf}}
      OxLaboClient.showModaleCriticalResult('{{$id_sejours}}');
    {{/if}}
  });

  function markAsSelected(element) {
    if (!element) {
      return;
    }
    element.up("tr").addUniqueClassName("selected");
  }

  viewScoringFormsService = function(service_id, date) {
    var url = new Url('soins', 'vw_scoring_forms_service');
    url.addParam('service_id', service_id);
    url.addParam('date', date);
    url.popup(800, 500, "Bilan par service");
  };

  checkAnesth = function(oField) {
    // Recuperation de la liste des anesthésistes
    var anesthesistes = {{$anesthesistes|@json}};

    var oForm = getForm("selService");
    var praticien_id = $V(oForm.praticien_id);
    var service_id   = $V(oForm.service_id);

    if (oField.name == "service_id"){
      if (anesthesistes.include(praticien_id)) {
        $V(oForm.praticien_id, '', false);
      }
    }

    if (oField.name == "praticien_id"){
      if (anesthesistes.include(praticien_id)) {
        $V(oForm.service_id, '', false);
      }
    }
  }

  savePref = function(form) {
    var formPref = getForm('editPrefServiceSoins');
    var formService = getForm('selService');
    var service_id = $V(form.default_service_id);

    var default_service_id_elt = formPref.elements['pref[default_services_id]'];
    var default_service_id = $V(default_service_id_elt).evalJSON();
    default_service_id.g{{$g}} = service_id;
    $V(default_service_id_elt, Object.toJSON(default_service_id));
    return onSubmitFormAjax(formPref, function() {
      Control.Modal.close();
      $V(formService.service_id, service_id);
    });
  }

  {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
    sortBySejour = function(order_col, order_way) {
      var form = getForm('selService');
      $V(form.order_col_sejour, order_col);
      $V(form.order_way_sejour, order_way);
      appFineClient.refreshDashboard();
    };
  {{/if}}

</script>

{{assign var=show_confirmation value="mpm general confirmation"|gconf}}
{{assign var=manual_alerts value="soins Observations manual_alerts"|gconf}}

<form name="form_prescription" action="" method="get">
  <input type="hidden" name="sejour_id" value="{{$object->_id}}" />
</form>

<table class="main">
  <tr>
    <td>
      {{assign var=largeur_colonne value=240}}
      {{if $isImedsInstalled}}{{math equation="x+30" x=$largeur_colonne assign=largeur_colonne}}{{/if}}
        {{math equation="x+25" x=$largeur_colonne assign=largeur_colonne}}
      <table class="form me-align-auto me-left-part-soin" id="left-column" style="width:{{$largeur_colonne}}px;">
        <tr>
          <th class="title">

            <form name="editPrefVueSejour" method="post" style="float: left">
              <input type="hidden" name="m" value="admin" />
              <input type="hidden" name="dosql" value="do_preference_aed" />
              <input type="hidden" name="user_id" value="{{$app->user_id}}" />
              <input type="hidden" name="pref[vue_sejours]" value="global" />
              <input type="hidden" name="postRedirect" value="m=soins&tab=vwSejours&viewMode={{$mode}}" />
              <button type="submit" class="change notext">Vue par défaut</button>
            </form>

            {{$date|date_format:$conf.longdate}}
            <form action="?" name="changeDate" method="get">
              <input type="hidden" name="m" value="{{$m}}" />
              <input type="hidden" name="tab" value="{{$tab}}" />
              <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
            </form>
            {{if !"soins Sejour select_services_ids"|gconf && $service_id && $service_id != "NP" && (@$modules.dPplanningOp->_can->admin || ("soins UserSejour can_edit_user_sejour"|gconf && @$modules.dPplanningOp->_can->edit))}}
              {{assign var=responsable_jour value='Ox\Mediboard\Hospi\CAffectationUserService::loadResponsableJour'|static_call:$service_id:$date}}
              <button  type="button"class="mediuser_black notext" onclick="Soins.reponsableJour('{{$date}}', '{{$service_id}}', 'selService');"
                      style="margin-right: 5px;{{if !$responsable_jour->_id}}opacity: 0.6;{{/if}}"
                      onmouseover="ObjectTooltip.createDOM(this, 'responsable_jour-{{$date}}-{{$service_id}}');"></button>
              {{if $responsable_jour->_id}}
                <span class="countertip" style="margin-top:1px;margin-left: -10px;"><span>1</span></span>
              {{/if}}
              <div style="display: none" id="responsable_jour-{{$date}}-{{$service_id}}" class="{{if !$responsable_jour->_id}}empty{{/if}}">
                {{if $responsable_jour->_id}}
                  {{tr}}CAffectationUserService.day{{/tr}}: {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$responsable_jour->_ref_user}}
                {{else}}
                  {{tr}}CUserSejour.none_responsable{{/tr}}
                {{/if}}
              </div>
            {{/if}}
          </th>
        </tr>

        <tr>
          <td style="padding: 0;">
            <form name="selService" action="?" method="get">
              <input type="hidden" name="m" value="{{$m}}" />
              <input type="hidden" name="tab" value="{{$tab}}" />
              <input type="hidden" name="sejour_id" value="" />
              <input type="hidden" name="date" value="{{$date}}" />

              {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
                <input type="hidden" name="order_col_sejour" value="{{$order_col_sejour}}" onchange="appFineClient.refreshDashboard()"/>
                <input type="hidden" name="order_way_sejour" value="{{$order_way_sejour}}" onchange="appFineClient.refreshDashboard()"/>
              {{/if}}

              <table class="main layout">
                <tr>
                  <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next()); updatePatientsListHeight();" style="padding: 0;"></td>
                  <td style="padding: 0;">
                    <table class="main form me-no-align me-no-box-shadow">
                      <tr>
                        {{me_form_field animated=false nb_cells=2 label="Display-mode"}}
                          <select name="mode" onchange="this.form.submit()" style="width:145px">
                            <option value="0" {{if $mode == 0}}selected{{/if}}>{{tr}}Instant view{{/tr}}</option>
                            <option value="1" {{if $mode == 1}}selected{{/if}}>{{tr}}Day view{{/tr}}</option>
                          </select>
                        {{/me_form_field}}
                        <td style="padding: 0;" class="narrow"></td>
                      </tr>

                      <tr>
                        <th class="me-no-display">
                          {{if !"soins Sejour select_services_ids"|gconf}}
                            <label for="service_id">
                              <button type="button" class="search notext me-tertiary" title="Service par défaut" onclick="Modal.open('select_default_service', { showClose: true, title: 'Service par défaut' })"></button>
                              Service
                            </label>
                          {{/if}}
                        </th>

                        {{if "soins Sejour select_services_ids"|gconf}}
                          <td>
                            <button type="button" class="search me-tertiary"
                                    onclick="Soins.selectServices('{{if $current_m === 'dPboard'}}tdb{{else}}soins{{/if}}');">
                              Services
                            </button>
                            {{mb_include module=soins template=vw_select_default_service}}
                          </td>
                        {{else}}
                          {{me_form_field nb_cells=2 animated=false}}
                            <select name="service_id" class="me-max-width-100" onchange="checkAnesth(this); if (this.form.func_id) { this.form.func_id.value = ''; } if (this.form.discipline_id) { this.form.discipline_id.value = ''; } this.form.submit()" style="max-width: 145px;">
                              <option value="">&mdash; Service</option>
                              {{foreach from=$services item=curr_service}}
                                <option value="{{$curr_service->_id}}" {{if $curr_service->_id == $service_id}}selected{{/if}}>{{$curr_service->nom}}</option>
                              {{/foreach}}
                              <option value="NP" {{if $service_id == "NP"}}selected{{/if}}>Non placés</option>
                            </select>
                          {{/me_form_field}}
                          <td style="padding: 0;" class="me-padding-right-8 narrow">
                            <button type="button" class="search notext me-inline-block" style="display: none" title="Service par défaut" onclick="Modal.open('select_default_service', { showClose: true, title: 'Service par défaut' })"></button>
                            {{mb_include module=soins template=vw_select_default_service}}
                          </td>
                        {{/if}}
                      </tr>

                      <tr>
                        {{me_form_field animated=false nb_cells=2 label="common-Practitioner" }}
                          <select name="praticien_id" onchange="checkAnesth(this); if (this.form.func_id) { this.form.func_id.value = ''; } if (this.form.discipline_id) { this.form.discipline_id.value = ''; } this.form.submit();" style="width: 145px;">
                            <option value="none">&mdash; Choix du praticien</option>
                            {{mb_include module=mediusers template=inc_options_mediuser selected=$praticien_id list=$praticiens}}
                          </select>
                        {{/me_form_field}}
                        <td style="padding: 0;" class="narrow"></td>
                      </tr>

                      <tr>
                        {{me_form_field animated=false nb_cells=2 mb_class="CSejour" mb_field="type"}}
                        {{assign var=type_admission value=$object->_specs.type}}
                          <select name="type" onchange="this.form.submit();" style="width: 145px;">
                            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                            {{foreach from=$type_admission->_locales key=key item=_type}}
                              {{if !in_array($key, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:null) && $key != "exte"}}
                                <option value="{{$key}}" {{if $key == $type_admission}}selected{{/if}}>{{$_type}}</option>
                              {{/if}}
                            {{/foreach}}
                          </select>
                        {{/me_form_field}}
                        <td style="padding: 0;" class="narrow"></td>
                      </tr>

                      {{if 'soins dossier_soins display_filter_functions_discipline'|gconf}}
                        <tr>
                          {{me_form_field animated=false nb_cells=2 mb_class="COperation" mb_field="_func_id"}}
                            <select name="func_id" onchange="this.form.praticien_id.value = 'none';
                            {{if !"soins Sejour select_services_ids"|gconf}} this.form.service_id.value = ''; {{/if}} if (this.form.discipline_id) { this.form.discipline_id.value = ''; } this.form.submit();" style="width: 145px;">
                              <option value="">&mdash; Tous les cabinets</option>
                              {{mb_include module=mediusers template=inc_options_function list=$listFuncs selected=$function_id}}
                            </select>
                          {{/me_form_field}}
                          <td style="padding: 0;" class="narrow"></td>
                        </tr>

                        <tr>
                          {{me_form_field animated=false nb_cells=2 mb_class="COperation" mb_field="_specialite" }}
                            <select name="discipline_id" onchange="this.form.praticien_id.value = 'none';
                            {{if !"soins Sejour select_services_ids"|gconf}} this.form.service_id.value = ''; {{/if}} if (this.form.func_id) { this.form.func_id.value = ''; } this.form.submit();" style="width: 145px;">
                              <option value="">&mdash; Toutes les spécialités</option>
                              {{foreach from=$listDisciplines item=curr_disc}}
                                <option value="{{$curr_disc->discipline_id}}" {{if $curr_disc->discipline_id == $discipline_id }}selected{{/if}}>
                                  {{$curr_disc->_view}}
                                </option>
                              {{/foreach}}
                            </select>
                          {{/me_form_field}}
                          <td style="padding: 0;" class="narrow"></td>
                        </tr>
                      {{/if}}

                      <tr>
                        {{me_form_field nb_cells=2 label="CPatient"}}
                          <input type="text" size="20" onkeyup="Soins.filterFullName(this, 'list_sejours', 1);" id="filter-patient-name" />
                        {{/me_form_field}}
                        <td style="padding: 0;" class="narrow"></td>
                      </tr>

                      {{if $app->_ref_user->isInfirmiere() || $app->_ref_user->isAideSoignant() || $app->_ref_user->isSageFemme() || $app->_ref_user->isKine() || $app->_ref_user->isPraticien()}}
                        <tr>
                          {{me_form_bool nb_cells=2 label="My-Patient" label_suffix="($count_my_patient)"}}
                            <input type="checkbox" name="change_patient" value="{{if $my_patient == 1}}0{{else}}1{{/if}}" {{if $my_patient == 1}}checked{{/if}} onchange="$V(this.form.my_patient, this.checked?1:0);"/>
                          {{/me_form_bool}}
                          <td style="padding: 0;" class="narrow">
                            <input type="hidden" name="my_patient" value="{{$my_patient}}" onchange="this.form.submit();"/>
                          </td>
                        </tr>
                      {{/if}}
                    </table>
                  </td>
                </tr>
              </table>
            </form>

            <form name="editPrefServiceSoins" method="post">
              <input type="hidden" name="m" value="admin" />
              <input type="hidden" name="dosql" value="do_preference_aed" />
              <input type="hidden" name="user_id" value="{{$app->user_id}}" />
              {{assign var=default_services_id value="{}"}}
              {{if isset($app->user_prefs.default_services_id|smarty:nodefaults)}}
                {{assign var=default_services_id value=$app->user_prefs.default_services_id}}
              {{/if}}
              <input type="hidden" name="pref[default_services_id]" value="{{$default_services_id|html_entity_decode}}" />
            </form>
          </td>
        </tr>

        {{if $_is_praticien && ($dnow == $date)}}
          <tr>
            <td class="button">
              <button type="button" class="search" title="{{$visites.non_effectuee|@count}} visite(s) non effectuée(s)" onclick="seeVisitesPrat();" id="visites_jour_prat">
                Mes visites
              </button>
            </td>
          </tr>
        {{/if}}
        <tr>
          <td class="me-border-top" style="padding: 0;">
            {{me_scroller old_style="overflow: auto; height: 500px; position: relative;"}}
              <table class="tbl me-no-box-shadow" id="list_sejours">
                {{foreach from=$sejoursParService key=_service_id item=service}}
                  {{if array_key_exists($_service_id, $services)}}
                    <tr class="me-dossier-soin-service-name">
                      {{assign var=_service value=$services.$_service_id}}
                      <th colspan="6" class="title">
                        {{$_service}}
                      </th>
                    </tr>
                    <tr class="me-dossier-soin-service-actions">
                      <th colspan="6" id="me-th-header">

                        {{assign var=buttons_list value=""}}

                        {{if 'forms'|module_active}}
                          {{if 'soins dossier_soins display_scoring_forms'|gconf}}
                            {{me_button label="scores" icon=search old_class=compact
                                        onclick="viewScoringFormsService('`$_service_id`','`$date`')"}}
                          {{/if}}

                          {{if 'soins dossier_soins display_mandatory_forms'|gconf}}
                            {{me_button label="forms-action-Search mandatory form|pl" icon=lookup old_class="notext compact"
                                        onclick="ExObject.searchMandatoryExClasses('`$date`', '`$_service_id`')"}}
                          {{/if}}
                        {{/if}}

                        {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
                          {{me_button label=CAppFine icon=appFine onclick="appFineClient.showDashboard()"}}
                        {{/if}}

                        {{me_button label="Feuille trans." icon=print onclick="Soins.feuilleTransmissions('$_service_id')"}}

                        {{if "dPprescription"|module_active}}
                          {{me_button label="Bilan" icon=print onclick="Soins.viewBilanService('$_service_id','$date')"}}
                        {{/if}}

                        {{me_dropdown_button button_label=Options button_icon=opt button_class="notext me-tertiary me-dark"
                          container_class="me-dropdown-button-right"}}
                      </th>
                    </tr>
                    {{foreach from=$service->_ref_chambres item=curr_chambre}}
                      {{foreach from=$curr_chambre->_ref_lits item=curr_lit}}
                        <tr>
                          <th class="category {{if !$curr_lit->_ref_affectations|@count}}opacity-50{{/if}}" colspan="6" style="font-size: 0.9em;">
                            {{if "soins CLit align_right"|gconf}}
                              <span style="float: left;">{{$curr_chambre}}</span>
                              <span style="float: right;">{{$curr_lit->_shortview}}</span>
                            {{else}}
                              <span style="float: left;">{{$curr_chambre}} - {{$curr_lit->_shortview}}</span>
                            {{/if}}
                          </th>
                        </tr>
                        {{foreach from=$curr_lit->_ref_affectations item=curr_affectation}}
                          {{if $curr_affectation->_ref_sejour->_id != ""}}
                            {{assign var=sejour value=$curr_affectation->_ref_sejour}}
                            <tr class="{{if $object->_id == $curr_affectation->_ref_sejour->_id}}selected{{/if}} {{$sejour->type}} {{if $curr_affectation->_in_permission}}opacity-50{{/if}}">
                              <td style="padding: 0;" class="">
                                <button class="lookup notext me-tertiary me-btn-small me-dark" style="margin:0;" onclick="popEtatSejour({{$sejour->_id}});">
                                  {{tr}}Lookup{{/tr}}
                                </button>
                                {{if @$modules.dPplanningOp->_can->admin || ("soins UserSejour can_edit_user_sejour"|gconf && @$modules.dPplanningOp->_can->edit)}}
                                  <button class="mediuser_black notext me-tertiary me-btn-small me-dark" onclick="Soins.paramUserSejour('{{$curr_affectation->sejour_id}}', '{{$service->_id}}', 'selService', '{{$date}}');"
                                          style="margin-right: 5px;{{if !$sejour->_ref_users_sejour|@count}}opacity: 0.6;{{/if}}"
                                          onmouseover="ObjectTooltip.createDOM(this, 'affectation_CSejour-{{$sejour->_id}}');"></button>
                                  {{if $sejour->_ref_users_sejour|@count}}
                                    <span class="countertip" style="margin-top:1px;margin-left: -10px;">
                                      <span>{{$sejour->_ref_users_sejour|@count}}</span>
                                    </span>
                                  {{/if}}
                                  {{mb_include module=planningOp template=vw_user_sejour_table}}
                                {{/if}}
                              </td>

                              <td class="text">
                                {{if "nouveal"|module_active && "nouveal general active_prm"|gconf}}
                                  {{assign var=sejour_id value=$sejour->_id}}
                                  <span style="float: right;">
                                    {{mb_include module=nouveal template=inc_vw_etat_patient}}
                                  </span>
                                {{/if}}
                                {{assign var=aff_next value=$curr_affectation->_ref_next}}
                                {{assign var=sejour value=$curr_affectation->_ref_sejour}}
                                {{assign var=sejour_nda value=$sejour->_NDA}}
                                <a class="text" href="#1"
                                   onclick="loadViewSejour('{{$sejour->_id}}',  '{{$date}}', this);{{if "oxLaboClient"|module_active && "oxLaboClient alert_result_critical modal_alert_result_critical"|gconf && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}OxLaboClient.showModaleCriticalResult('{{$sejour->_id}}'){{/if}}">
                                  {{if $curr_affectation->_ref_parent_affectation && $curr_affectation->lit_id == $curr_affectation->_ref_parent_affectation->lit_id}}
                                    <span style="font-size: 1.5em">&rarrhk;</span>
                                  {{/if}}
                                  <span class="CPatient-view {{if !$sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $sejour->septique}}septique{{/if}}" onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
                                    {{$sejour->_ref_patient}}
                                  </span>

                                  {{mb_include module=patients template=inc_icon_bmr_bhre patient=$sejour->_ref_patient}}
                                  {{if $sejour->presence_confidentielle}}
                                    {{mb_include module=planningOp template=inc_badge_sejour_conf}}
                                  {{/if}}
                                  {{if $sejour->_ref_patient->_homonyme}}
                                    {{mb_include module=dPpatients template=patient_state/inc_flag_homonyme}}
                                  {{/if}}
                                </a>

                                {{if $curr_affectation->_id}}
                                  {{mb_include module=planningOp template=inc_icon_autorisation_permission}}
                                {{/if}}

                                {{if $sejour->_ref_prescriptions && array_key_exists('sejour', $sejour->_ref_prescriptions)}}
                                  {{assign var=prescription value=$sejour->_ref_prescriptions.sejour}}

                                  <div class="text" style="font-size: 12pt;">
                                    {{mb_include module=prescription template=vw_line_important lines=$prescription->_ref_lines_important}}
                                  </div>
                                {{/if}}
                              </td>

                              <td>
                                {{if $sejour->_ref_prescriptions && array_key_exists('sejour', $sejour->_ref_prescriptions)}}
                                  {{assign var=prescription value=$sejour->_ref_prescriptions.sejour}}
                                  {{assign var=prescription_guid value=$prescription->_guid}}
                                  {{if $prescription->_id}}
                                    {{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CPrescriptionAlerteHandler'}}
                                      <span id="span-icon-alert-medium-{{$prescription_guid}}">
                                        {{mb_include module=system template=inc_icon_alerts object=$prescription nb_alerts=$prescription->_count_alertes
                                        callback="function() { compteurAlerte('medium', '$prescription_guid')}"}}
                                      </span>
                                    {{elseif $prescription->_count_fast_recent_modif}}
                                      <div class="me-bulb-info me-bulb-ampoule" onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')">
                                        {{mb_include module=system template=inc_vw_counter_tip count=$prescription->_count_fast_recent_modif top="-5px" right="-15px"}}
                                      </div>
                                    {{/if}}

                                    <span id="span-icon-alert-high-{{$prescription_guid}}">
                                      {{mb_include module=system template=inc_icon_alerts object=$prescription level="high" nb_alerts=$prescription->_count_urgences
                                      callback="function() { compteurAlerte('high', '$prescription_guid')}"}}
                                    </span>
                                    {{if $show_confirmation}}
                                      {{assign var=really_show_confirmation value=true}}
                                      {{if $prescription->_alert_confirmation === null}}
                                        {{assign var=really_show_confirmation value=false}}
                                      {{/if}}
                                      <i id="confirmation_lines_{{$prescription->_id}}"
                                        {{if $really_show_confirmation}}
                                        class="fa fa-{{if $prescription->_alert_confirmation}}times{{else}}check{{/if}}-circle"
                                        style="color: #{{if $prescription->_alert_confirmation}}800{{else}}080{{/if}}; font-size: 1.2em;"
                                        title="{{tr}}CPrescription-{{if $prescription->_alert_confirmation}}alert_{{/if}}lines_confirme{{/tr}}"
                                        {{/if}}></i>
                                    {{/if}}
                                  {{/if}}
                                {{/if}}
                                {{if $manual_alerts}}
                                  {{mb_include module=system template=inc_icon_alerts object=$sejour tag=observation show_empty=1 show_span=1 event=onmouseover img_ampoule="ampoule_rose"}}
                                {{/if}}
                              </td>
                              <td style="padding: 1px;" >
                                {{if array_key_exists($sejour_nda, $labo_alert_by_nda) && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
                                  <span id="OxLaboAlert_{{$sejour_nda}}">
                                    {{mb_include module=oxLaboClient template=vw_alerts object=$sejour object_id=$sejour->_id object_class=$sejour->_class response_id=$sejour_nda response_type='nda' nb_alerts=$labo_alert_by_nda.$sejour_nda.total alerts=$labo_alert_by_nda.$sejour_nda}}
                                  </span>
                                {{/if}}
                                  {{if array_key_exists($sejour_nda, $new_labo_alert_by_nda) && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
                                    <span id="OxLaboNewAlert_{{$sejour_nda}}">
                                    {{mb_include module=oxLaboClient template=vw_alerts object=$sejour object_id=$sejour->_id object_class=$sejour->_class response_id=$sejour_nda response_type='nda' nb_alerts=$new_labo_alert_by_nda.$sejour_nda|@count alerts=$new_labo_alert_by_nda.$sejour_nda alert_new_result=true}}
                                  </span>
                                  {{/if}}
                                {{if $isImedsInstalled}}
                                  <div class="Imeds_button" onclick="loadViewSejour('{{$sejour->_id}}', '{{$date}}', this, 'Imeds');">
                                    {{mb_include module=Imeds template=inc_sejour_labo link="#"}}
                                  </div>
                                {{/if}}
                                {{mb_include module=dPfiles template=inc_icon_category_check object=$sejour}}
                              </td>

                              <td class="action text me-text-align-center me-ws-nowrap" style="padding: 1px;">
                                {{mb_include module=hospi template=inc_vw_icones_sejour}}
                                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien initials=border}}
                                  {{foreach from=$sejour->_ref_operations item=_interv}}
                                    {{if $_interv->_ref_anesth}}
                                      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_interv->_ref_anesth initials=border}}
                                    {{/if}}
                                    {{if $_interv->chir_id !=  $sejour->praticien_id && $_interv->_ref_praticien}}
                                      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_interv->_ref_praticien initials=border}}
                                    {{/if}}
                                  {{/foreach}}
                              </td>
                            </tr>
                          {{/if}}
                        {{/foreach}}
                      {{/foreach}}
                    {{/foreach}}
                    {{if $service->_ref_affectations_couloir && $service->_ref_affectations_couloir|@count != 0}}
                      <tr>
                        <th class="category" colspan="6">
                          Couloir
                        </th>
                      </tr>
                      {{foreach from=$service->_ref_affectations_couloir item=curr_affectation}}
                        {{if $curr_affectation->_ref_sejour->_id != ""}}
                          {{assign var=sejour value=$curr_affectation->_ref_sejour}}
                          <tr class="{{if $object->_id == $curr_affectation->_ref_sejour->_id}}selected{{/if}} {{$sejour->type}}">
                            <td style="padding: 0;">
                              <button class="lookup notext me-tertiary me-btn-small me-dark" style="margin:0;" onclick="popEtatSejour({{$sejour->_id}});">
                                {{tr}}Lookup{{/tr}}
                              </button>
                              {{if @$modules.dPplanningOp->_can->admin}}
                                <button class="mediuser_black notext me-tertiary me-btn-small me-dark" style="margin-right: 5px;"
                                        onclick="Soins.paramUserSejour('{{$curr_affectation->sejour_id}}', '{{$service->_id}}', 'selService', '{{$date}}');"
                                        onmouseover="ObjectTooltip.createDOM(this, 'affectation_CSejour-{{$sejour->_id}}');">
                                </button>
                                <span class="countertip" style="margin-top:1px;margin-left: -10px;">
                        <span class="{{if !$sejour->_ref_users_sejour|@count}}empty{{/if}}">{{$sejour->_ref_users_sejour|@count}}</span>
                      </span>
                                <div style="display: none;" id="affectation_CSejour-{{$curr_affectation->sejour_id}}">
                                  <table class="tbl">
                                    {{foreach from=$sejour->_ref_users_by_type item=_users key=type}}
                                      <tr>
                                        <th>{{tr}}CUserSejour.{{$type}}{{/tr}}</th>
                                      </tr>
                                      {{foreach from=$_users item=_user}}
                                        <tr>
                                          <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user->_ref_user}}</td>
                                        </tr>
                                        {{foreachelse}}
                                        <tr>
                                          <td class="empty">{{tr}}CUserSejour.none{{/tr}}</td>
                                        </tr>
                                      {{/foreach}}
                                    {{/foreach}}
                                  </table>
                                </div>
                              {{/if}}
                            </td>

                            <td class="text">
                              {{if "nouveal"|module_active && "nouveal general active_prm"|gconf}}
                                {{assign var=sejour_id value=$sejour->_id}}
                                <span style="float: right;">
                                    {{mb_include module=nouveal template=inc_etat_patient}}
                                </span>
                              {{/if}}
                              {{assign var=aff_next value=$curr_affectation->_ref_next}}
                              {{assign var=sejour value=$curr_affectation->_ref_sejour}}
                              {{assign var=sejour_nda value=$sejour->_NDA}}
                              <a class="text" href="#1"
                                 onclick="loadViewSejour('{{$sejour->_id}}',  '{{$date}}', this);{{if "oxLaboClient"|module_active && "oxLaboClient alert_result_critical modal_alert_result_critical"|gconf && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}OxLaboClient.showModaleCriticalResult('{{$sejour->_id}}'){{/if}}">
                      <span class="CPatient-view {{if !$sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $sejour->septique}}septique{{/if}}" onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
                        {{$sejour->_ref_patient}}
                          {{if $sejour->_ref_patient->_homonyme}}
                              {{mb_include module=patients template=patient_state/inc_flag_homonyme}}
                          {{/if}}
                      </span>

                                {{mb_include module=patients template=inc_icon_bmr_bhre patient=$sejour->_ref_patient}}
                              </a>

                              {{if $curr_affectation->_id}}
                                {{mb_include module=planningOp template=inc_icon_autorisation_permission}}
                              {{/if}}
                            </td>

                            <td>
                              {{if $sejour->_ref_prescriptions && array_key_exists('sejour', $sejour->_ref_prescriptions)}}
                                {{assign var=prescription value=$sejour->_ref_prescriptions.sejour}}
                                {{assign var=prescription_guid value=$prescription->_guid}}
                                {{if $prescription->_id}}
                                  {{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CPrescriptionAlerteHandler'}}
                                    <span id="span-icon-alert-medium-{{$prescription_guid}}">
                                      {{mb_include module=system template=inc_icon_alerts object=$prescription nb_alerts=$prescription->_count_alertes
                                      callback="function() { compteurAlerte('medium', '$prescription_guid')}"}}
                                    </span>
                                    <span id="span-icon-alert-high-{{$prescription_guid}}">
                                      {{mb_include module=system template=inc_icon_alerts object=$prescription level="high" nb_alerts=$prescription->_count_urgences
                                      callback="function() { compteurAlerte('high', '$prescription_guid')}"}}
                                    </span>
                                  {{elseif $prescription->_count_fast_recent_modif}}
                                    {{mb_include module=system template=inc_bulb img_ampoule="ampoule" event_trigger="onmouseover"
                                                 event_function="ObjectTooltip.createEx(this, '`$prescription->_guid`') "
                                                 alert_nb=$prescription->_count_fast_recent_modif alert_top="-5px" alert_left="-15px"}}
                                  {{/if}}
                                  {{if $show_confirmation}}
                                    <i class="fa fa-{{if $prescription->_alert_confirmation}}times{{else}}check{{/if}}-circle"
                                       style="color: #{{if $prescription->_alert_confirmation}}800{{else}}080{{/if}}; font-size: 1.2em;"
                                       title="{{tr}}CPrescription-{{if $prescription->_alert_confirmation}}alert_{{/if}}lines_confirme{{/tr}}"></i>
                                  {{/if}}
                                {{/if}}
                              {{/if}}
                              {{if $manual_alerts}}
                                {{mb_include module=system template=inc_icon_alerts object=$sejour tag=observation show_empty=1 show_span=1 event=onmouseover img_ampoule="ampoule_rose"}}
                              {{/if}}
                            </td>
                            <td style="padding: 1px;" >
                              {{if array_key_exists($sejour_nda, $labo_alert_by_nda) && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
                                <span id="OxLaboAlert_{{$sejour_nda}}">
                                  {{mb_include module=oxLaboClient template=vw_alerts object=$sejour object_id=$sejour->_id object_class=$sejour->_class response_id=$sejour_nda response_type='nda' nb_alerts=$labo_alert_by_nda.$sejour_nda.total alerts=$labo_alert_by_nda.$sejour_nda}}
                                </span>
                              {{/if}}
                              {{if array_key_exists($sejour_nda, $new_labo_alert_by_nda) && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
                                <span id="OxLaboNewAlert_{{$sejour_nda}}">
                                  {{mb_include module=oxLaboClient template=vw_alerts object=$sejour object_id=$sejour->_id object_class=$sejour->_class response_id=$sejour_nda response_type='nda' nb_alerts=$new_labo_alert_by_nda.$sejour_nda|@count alerts=$new_labo_alert_by_nda.$sejour_nda alert_new_result=true}}
                                </span>
                              {{/if}}
                              {{if $isImedsInstalled}}
                                <div class="Imeds_button" onclick="loadViewSejour('{{$sejour->_id}}', '{{$date}}', this, 'Imeds');">
                                  {{mb_include module=Imeds template=inc_sejour_labo link="#"}}
                                </div>
                              {{/if}}
                              {{mb_include module=dPfiles template=inc_icon_category_check object=$sejour}}
                            </td>

                            <td class="action text" style="padding: 1px;">
                              {{mb_include module=hospi template=inc_vw_icones_sejour}}
                              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien initials=border}}
                                {{foreach from=$sejour->_ref_operations item=_interv}}
                                  {{if $_interv->_ref_anesth}}
                                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_interv->_ref_anesth initials=border}}
                                  {{/if}}
                                  {{if $_interv->chir_id !=  $sejour->praticien_id && $_interv->_ref_praticien}}
                                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_interv->_ref_praticien initials=border}}
                                  {{/if}}
                                {{/foreach}}
                            </td>
                          </tr>
                        {{/if}}
                      {{/foreach}}
                    {{/if}}
                  {{/if}}
                {{/foreach}}

                <!-- Cas de l'affichage par praticien -->
                {{if $praticien_id}}
                  {{if array_key_exists('NP', $sejoursParService)}}
                    <tr>
                      <th class="title" colspan="6">Non placés</th>
                    </tr>
                    {{foreach from=$sejoursParService.NP item=_sejour_NP}}
                      {{mb_include module="hospi" template="inc_vw_sejour_np" curr_sejour=$_sejour_NP}}
                    {{/foreach}}
                  {{/if}}
                {{/if}}

                <!-- Cas de l'affichage par service -->
                {{if $service_id || in_array('NP', $services_ids)}}
                  {{foreach from=$groupSejourNonAffectes key=group_name item=sejourNonAffectes}}
                    <tr>
                      <th class="title" colspan="6">
                        {{tr}}CSejour.groupe.{{$group_name}}{{/tr}}
                      </th>
                    </tr>
                    {{foreach from=$sejourNonAffectes item=curr_sejour}}
                      {{mb_include module="hospi" template="inc_vw_sejour_np"}}
                    {{/foreach}}
                  {{/foreach}}
                {{/if}}
              </table>
            {{/me_scroller}}
          </td>
        </tr>
      </table>
    </td>
    <td style="width: 100%;">
      <div id="dossier_sejour" class="me-no-align">
        <div class="small-info">
          Veuillez sélectionner un séjour dans la liste de gauche pour afficher
          le dossier de soin du patient concerné.
        </div>
      </div>
    </td>
  </tr>
</table>
