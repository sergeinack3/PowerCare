{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=use_poste              value=$conf.dPplanningOp.COperation.use_poste}}
{{assign var=use_sortie_reveil_reel value="dPsalleOp COperation use_sortie_reveil_reel"|gconf}}
{{assign var=pref_pec_sspi          value=$app->user_prefs.pec_sspi_current_user}}
{{assign var=concentrator_session   value=false}}

{{if $require_check_list && !"dPsalleOp CDailyCheckList choose_moment_edit"|gconf}}
  <script>
    Main.add(function () {
      var elt = $('{{$type}}');

      elt._periodicallyUpdated = false;
      elt._updater.stop();
    });
  </script>
  <table class="main layout">
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
            list_chirs=$listChirs
            list_anesths=$listAnesths
            personnel=$personnels}}
          </div>
        </td>
      {{/foreach}}
    </tr>
  </table>
  {{mb_return}}
{{/if}}

{{if ($require_check_list_close && $date_close_checklist|date_format:$conf.date != $date|date_format:$conf.date)
|| ($date_close_checklist|date_format:$conf.date == $date|date_format:$conf.date && "dPsalleOp CDailyCheckList multi_check_sspi"|gconf)}}
  {{mb_include module=salleOp template=inc_last_valid_checklist date_checklist=$date_close_checklist object_id=$bloc_id type='fermeture_sspi'}}
{{/if}}
{{if ("dPsalleOp CDailyCheckList choose_moment_edit"|gconf && $require_check_list && $date_open_checklist|date_format:$conf.date != $date|date_format:$conf.date)
|| ($date_open_checklist|date_format:$conf.date == $date|date_format:$conf.date && "dPsalleOp CDailyCheckList multi_check_sspi"|gconf)}}
  {{mb_include module=salleOp template=inc_last_valid_checklist date_checklist=$date_open_checklist object_id=$bloc_id type='ouverture_sspi'}}
{{/if}}

{{assign var=use_concentrator value=false}}
{{assign var=current_session  value=""}}
{{if "patientMonitoring"|module_active && "patientMonitoring CMonitoringConcentrator active"|gconf}}
  {{assign var=use_concentrator value=true}}
{{/if}}

<script>
  Main.add(function () {
    {{if $isImedsInstalled}}
    ImedsResultsWatcher.loadResults();
    {{/if}}
  });

  orderTabreveil = function (col, way) {
    orderTabReveil(col, way, 'reveil');
  };

  savePrefAndReload = function (element) {
    var form = getForm("editPrefPecSSPI");
    $V(form.elements["pref[pec_sspi_current_user]"], element.checked ? 1 : 0);
    return onSubmitFormAjax(form, function () {
      refreshTabReveil('reveil');
    });
  };
</script>

<!-- Formulaire de sauvegarde en préférence utilisateur -->
<form name="editPrefPecSSPI" method="post" class="prepared">
  <input type="hidden" name="m" value="admin"/>
  <input type="hidden" name="dosql" value="do_preference_aed"/>
  <input type="hidden" name="user_id" value="{{$app->user_id}}"/>
  <input type="hidden" name="pref[pec_sspi_current_user]" value=""/>
</form>

<table class="tbl me-no-align me-small">
  <tr>
    <th>{{mb_colonne class="COperation" field="salle_id" order_col=$order_col order_way=$order_way function=orderTabreveil}}</th>
    <th>{{mb_colonne class="COperation" field="chir_id" order_col=$order_col order_way=$order_way function=orderTabreveil}}</th>
    <th class="narrow">
        {{mb_colonne class="COperation" field="_patient" order_col=$order_col order_way=$order_way function=orderTabreveil}}
    </th>
    <th class="narrow me-small-fields">
        {{me_form_field}}
          <input type="text" name="_seek_patient_preop" value="" class="seek_patient" onkeyup="seekPatient(this);" onchange="seekPatient(this);" />
        {{/me_form_field}}
    </th>
      {{if "dPsalleOp SSPI_cell see_ctes"|gconf}}
        <th>{{tr}}SSPI_cell.see_ctes{{/tr}}</th>
      {{/if}}
    <th>
        {{mb_colonne class="COperation" field="libelle" order_col=$order_col order_way=$order_way function=orderTabreveil}}
    </th>
    <th>
        {{mb_colonne class="COperation" field="cote" order_col=$order_col order_way=$order_way function=orderTabreveil}}
    </th>
    {{if "dPsalleOp SSPI_cell see_type_anesth"|gconf}}
      <th>{{mb_colonne class="COperation" field="type_anesth" order_col=$order_col order_way=$order_way function=orderTabreveil}}</th>
    {{/if}}
    {{if "dPsalleOp SSPI_cell see_localisation"|gconf}}
      <th>{{tr}}SSPI.Chambre{{/tr}}</th>
    {{/if}}
    <th class="narrow">{{tr}}CTraitement-dossier_medical_id-desc{{/tr}}</th>
    {{if $personnels !== null}}
      <th>{{mb_colonne class="COperation" field="sortie_salle" order_col=$order_col order_way=$order_way function=orderTabreveil}}</th>
    {{/if}}
    <th>{{mb_colonne class="COperation" field="entree_reveil" order_col=$order_col order_way=$order_way function=orderTabreveil}}</th>
    {{if $use_poste || $use_concentrator}}
      <th>
      {{if $use_poste}}
        {{mb_colonne class="COperation" field="poste_sspi_id" order_col=$order_col order_way=$order_way function=orderTabreveil}}
      {{/if}}

      {{if !$use_poste && $use_concentrator}}
        <th class="narrow">Conc.</th>
      {{/if}}
      </th>
    {{/if}}
    <th>
      {{tr}}SSPI.Responsable{{/tr}}
      <input type="checkbox" name="_pec_sspi_current_user" onchange="savePrefAndReload(this);"
             {{if $pref_pec_sspi}}checked="checked"{{/if}}
             title="{{tr}}SSPI-msg-Limit the display of patients who are supported by the current user{{/tr}}">
    </th>
    {{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
      <th>{{tr}}CBrancardage{{/tr}}</th>
    {{/if}}
    {{if $use_sortie_reveil_reel}}
      <th>{{mb_colonne class="COperation" field="sortie_reveil_possible" order_col=$order_col order_way=$order_way function=orderTabreveil}}</th>
      <th
        style="width: 15%">{{mb_colonne class="COperation" field="sortie_reveil_reel" order_col=$order_col order_way=$order_way function=orderTabreveil}}</th>
    {{else}}
      <th>{{mb_colonne class="COperation" field="sortie_reveil_reel" order_col=$order_col order_way=$order_way function=orderTabreveil}}</th>
    {{/if}}
    <th class="narrow"></th>
  </tr>
  {{foreach from=$listOperations key=key item=_operation}}
    {{assign var=patient value=$_operation->_ref_patient}}
    {{assign var=sejour_id value=$_operation->sejour_id}}
    {{assign var=_operation_id value=$_operation->_id}}
    <tr>
      <td>{{$_operation->_ref_salle->_shortview}}</td>
      <td class="text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir classe="me-wrapped"}}
      </td>
      <td class="text" colspan="2">
        {{mb_include module=system template=inc_object_notes object=$_operation float="right"}}
        <div style="float: right;">
          {{if $isImedsInstalled}}
            {{mb_include module=Imeds template=inc_sejour_labo link="#1" sejour=$_operation->_ref_sejour float="none"}}
          {{/if}}
        </div>
        <span
          class="CPatient-view {{if !$_operation->_ref_sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $_operation->_ref_sejour->septique}}septique{{/if}}"
          onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
      {{$patient}}
      </span>
        {{mb_include module=patients template=inc_icon_bmr_bhre}}
      </td>
        {{if "dPsalleOp SSPI_cell see_ctes"|gconf}}
          <td class="text" style="text-align: center;">
              {{if $patient->_ref_constantes_medicales->poids}}
                  {{mb_value object=$patient->_ref_constantes_medicales field=poids}} kg
              {{else}}
                &mdash;
              {{/if}}
            /
              {{if $patient->_ref_constantes_medicales->taille}}
                  {{mb_value object=$patient->_ref_constantes_medicales field=taille}} cm
              {{else}}
                &mdash;
              {{/if}}
          </td>
        {{/if}}
      <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')">
          {{if $_operation->libelle}}
              {{$_operation->libelle}}
          {{else}}
              {{foreach from=$_operation->_ext_codes_ccam item=curr_code}}
                  {{$curr_code->code}}
              {{/foreach}}
          {{/if}}
        </span>
      </td>
      <td class="text">{{mb_value object=$_operation field="cote"}}</td>
      {{if "dPsalleOp SSPI_cell see_type_anesth"|gconf}}
        <td class="text">{{mb_value object=$_operation field="type_anesth"}}</td>
      {{/if}}
      {{if "dPsalleOp SSPI_cell see_localisation"|gconf}}
        <td class="text">
            {{mb_include module=hospi template=inc_placement_sejour sejour=$_operation->_ref_sejour which="curr"}}
        </td>
      {{/if}}
      <td>
        <button class="soins button notext me-tertiary me-small"
                onclick="showDossierSoins('{{$sejour_id}}','{{$_operation_id}}');">
          Dossier de soins
        </button>
        {{if $isImedsInstalled}}
          <button class="labo button notext me-tertiary me-small"
                  onclick="showDossierSoins('{{$sejour_id}}','{{$_operation_id}}','Imeds');">
            Labo
          </button>
        {{/if}}
        <button type="button" class="injection notext me-tertiary me-small"
                onclick="Operation.dossierBloc('{{$_operation_id}}', true, 'surveillance_sspi')">
          Dossier de bloc
        </button>
        {{mb_include module=patients template=inc_antecedents_allergies show_atcd=0 dossier_medical=$patient->_ref_dossier_medical patient_guid=$patient->_guid}}

        {{if $_operation->entree_reveil && $perop_lines_unsigned.$_operation_id > 0}}
          {{me_img_title src="warning.png" icon="warning" class="me-warning"}}
          {{tr var1=$perop_lines_unsigned.$_operation_id}}SSPI-msg-Perop prescription lines are unsigned %s{{/tr}}
          {{/me_img_title}}
        {{/if}}
      </td>
      <td>
        {{mb_value object=$_operation field="sortie_salle"}}
      </td>
      <td class="me-small-fields">
        <form name="editEntreeReveilReveilFrm{{$_operation_id}}" method="post" class="prepared">
          <input type="hidden" name="m" value="planningOp"/>
          <input type="hidden" name="dosql" value="do_planning_aed"/>
            {{mb_key object=$_operation}}
          <input type="hidden" name="del" value="0"/>
            {{if $_operation->_ref_sejour->type=="exte"}}
              -
            {{elseif ($modif_operation || $_operation->_modif_operation) && !$_operation->sortie_reveil_possible}}
                {{assign var=validate_datetimes    value='Ox\Mediboard\PlanningOp\COperation::getValidatingTimings'|static_call:"`$_operation->_id`":"entree_reveil"}}
                {{assign var=validate_datetime_min value=$validate_datetimes.min}}
                {{assign var=validate_datetime_max value=$validate_datetimes.max}}
                {{assign var=last_timing           value=$validate_datetimes.last_timing}}

                {{if $use_concentrator}}
                    {{assign var=current_session value='Ox\Mediboard\PatientMonitoring\CMonitoringSession::getCurrentSession'|static_call:"`$_operation`"}}
                {{/if}}

                {{mb_field object=$_operation field="entree_reveil" register=true form="editEntreeReveilReveilFrm$_operation_id" onchange="if (SalleOp.checkTimingOperation('`$_operation->_ref_sejour->entree`', '`$_operation->_ref_sejour->sortie`', this, '`$_operation->_id`', '`$last_timing`')) {submitReveilForm(this.form);}"}}
            {{else}}
                {{mb_value object=$_operation field="entree_reveil"}}
            {{/if}}
        </form>
      </td>
      {{if $use_poste || $use_concentrator}}
        <td class="me-small-fields">
          {{if $use_poste}}
            {{mb_include module=dPsalleOp template=inc_form_toggle_poste_sspi type="reveil" sspi_id=$sspi_id}}
          {{/if}}

          {{if $use_concentrator}}
            {{mb_include module=patientMonitoring template=inc_concentrator_session operation=$_operation type="sspi" bloc_id=$bloc_id
            callback="refreshTabReveil.curry('reveil')"}}
          {{/if}}
        </td>
      {{/if}}
      {{if $personnels !== null}}
        <td class="me-small-fields">
          <form name="selPersonnel{{$_operation_id}}" method="post" class="prepared">
            <input type="hidden" name="m" value="personnel"/>
            <input type="hidden" name="dosql" value="do_affectation_aed"/>
            <input type="hidden" name="del" value="0"/>
            <input type="hidden" name="object_id" value="{{$_operation_id}}"/>
            <input type="hidden" name="object_class" value="{{$_operation->_class}}"/>
            <input type="hidden" name="tag" value="reveil"/>
            <input type="hidden" name="realise" value="0"/>
            <select name="personnel_id" style="max-width: 120px;">
              <option value="">&mdash; Personnel</option>
              {{foreach from=$personnels item="personnel"}}
                <option value="{{$personnel->_id}}">{{$personnel->_ref_user}}</option>
              {{/foreach}}
            </select>
            <button type="button" class="add notext me-small"
                    onclick="onSubmitFormAjax(this.form, refreshTabReveil.curry('reveil'))">
              {{tr}}Add{{/tr}}
            </button>
          </form>
          {{foreach from=$_operation->_ref_affectations_personnel.reveil item=curr_affectation}}
            <br/>
            <form name="delPersonnel{{$curr_affectation->_id}}" method="post" class="prepared">
              <input type="hidden" name="m" value="personnel"/>
              <input type="hidden" name="dosql" value="do_affectation_aed"/>
              <input type="hidden" name="del" value="1"/>
              {{mb_key object=$curr_affectation}}
              <button type="button" class="trash notext me-tertiary me-small"
                      onclick="onSubmitFormAjax(this.form, refreshTabReveil.curry('reveil'))">
                {{tr}}Delete{{/tr}}
              </button>
            </form>
            {{$curr_affectation->_ref_personnel->_ref_user}}
          {{/foreach}}
        </td>
      {{/if}}
      {{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
        {{assign var=consult_anesth value=$_operation->_ref_consult_anesth}}
        <td>
          {{if $consult_anesth && $consult_anesth->_id}}
            <form name="addPatientDebout{{$_operation->_id}}" method="post" onsubmit="return onSubmitFormAjax(this);">
              {{mb_key   object=$consult_anesth}}
              {{mb_class object=$consult_anesth}}

              {{mb_label object=$consult_anesth field=accord_patient_debout_retour}}
              {{mb_field object=$consult_anesth field=accord_patient_debout_retour typeEnum=checkbox onchange="this.form.onsubmit()"}}
            </form>
            <br/>
          {{/if}}

          <div id="brancardage-{{$_operation->_guid}}">
            {{assign var=tagToLoad value="Ox\Mediboard\Brancardage\Utilities\CBrancardageGetUtility::STEP_BRANCARDAGE_INFINI"|constant}}
            {{mb_include module=brancardage template=inc_exist_brancard colonne="demandeBrancardage"
            object=$_operation brancardage_to_load=$tagToLoad}}
          </div>
        </td>
      {{/if}}
      <td class="button me-small-fields">
        {{if $modif_operation || $_operation->_modif_operation}}
          <form name="editSortieReveilReveilFrm{{$_operation_id}}" method="post" class="prepared">
            <input type="hidden" name="m" value="planningOp"/>
            <input type="hidden" name="dosql" value="do_planning_aed"/>
            {{mb_key object=$_operation}}
            <input type="hidden" name="del" value="0"/>
            {{mb_field object=$_operation field=sortie_locker_id hidden=1}}
            {{if $_operation->sortie_locker_id}}
              <span onmouseover="ObjectTooltip.createDOM(this, 'info_locker_{{$_operation_id}}')">
                {{mb_field object=$_operation field="sortie_reveil_possible" hidden=1}}
                {{mb_value object=$_operation field="sortie_reveil_possible"}}
            <button type="button" class="cancel notext me-tertiary me-dark me-small" title="Annuler la validation"
                    onclick="$V(this.form.sortie_reveil_possible, ''); $V(this.form.sortie_reveil_reel, ''); $V(this.form.sortie_locker_id, ''); submitSortie(this.form);"></button>
              </span>
              <div id="info_locker_{{$_operation_id}}" style="display: none">
                {{tr}}COperation-sortie_locker_id{{/tr}} {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_sortie_locker classe="me-wrapped"}}
                <span style="padding-left: 10px;">
              {{mb_include module=system template=inc_object_history object=$_operation}}
            </span>
              </div>
            {{else}}
              {{mb_field object=$_operation field=sortie_reveil_possible register=true form=editSortieReveilReveilFrm`$_operation_id` onchange="submitReveil(this.form)"}}
            {{/if}}
            {{if !$_operation->sortie_reveil_possible}}
              <button class="tick notext me-tertiary me-small" type="button"
                      onclick="if (!this.form.sortie_reveil_possible.value) { $V(this.form.sortie_reveil_possible, 'current', false); }; submitReveil(this.form);">{{tr}}Modify{{/tr}}</button>
            {{/if}}
          </form>
        {{else}}-{{/if}}

        {{mb_include module=forms template=inc_widget_ex_class_register_multiple object=$_operation cssStyle="display: inline-block;"}}
      </td>
      {{if $use_sortie_reveil_reel}}
        <td class="button me-small-fields">
          {{if $use_concentrator}}
            {{assign var=current_session value='Ox\Mediboard\PatientMonitoring\CMonitoringSession::getCurrentSession'|static_call:$_operation}}

            {{if $use_concentrator}}
              {{assign var=concentrator_session value=true}}
            {{/if}}
          {{/if}}
          <form name="editSortieReveilReelReveilFrm{{$_operation_id}}" method="post" class="prepared">
            <input type="hidden" name="m" value="planningOp"/>
            <input type="hidden" name="dosql" value="do_planning_aed"/>
            {{mb_key object=$_operation}}
            <input type="hidden" name="del" value="0"/>
            {{if $modif_operation}}
              {{mb_field object=$_operation field=sortie_reveil_reel register=true form="editSortieReveilReelReveilFrm$_operation_id" onchange="submitReveilForm(this.form);"}}
              {{if !$_operation->sortie_reveil_reel}}
                <button class="tick notext me-tertiary me-small" type="button"
                        onclick="if (!this.form.sortie_reveil_reel.value) {
                          $V(this.form.sortie_reveil_reel, 'current');
                        };
                        submitReveilForm(this.form, true, '{{$concentrator_session}}', '{{if $current_session && $current_session->_id}}{{$current_session->_id}}{{/if}}');">
                  {{tr}}Modify{{/tr}}
                </button>
              {{/if}}
            {{else}}
              {{mb_value object=$_operation field="sortie_reveil_reel"}}
            {{/if}}
          </form>
        </td>
      {{/if}}
      <td>
        <button type="button" class="print notext me-tertiary me-small"
                onclick="printDossier('{{$sejour_id}}', '{{$_operation_id}}')"></button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="23" class="empty">{{tr}}COperation.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

{{mb_include module=forms template=inc_widget_ex_class_register_multiple_end event_name=sortie_reveil object_class="COperation"}}
