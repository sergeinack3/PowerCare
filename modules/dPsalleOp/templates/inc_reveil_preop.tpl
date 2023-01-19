{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=use_poste value=$conf.dPplanningOp.COperation.use_poste}}

{{if $require_check_list && !"dPsalleOp CDailyCheckList choose_moment_edit"|gconf}}
<script>
  Main.add(function() {
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
|| ($date_close_checklist|date_format:$conf.date == $date|date_format:$conf.date && "dPsalleOp CDailyCheckList multi_check_preop"|gconf)}}
  {{mb_include module=salleOp template=inc_last_valid_checklist date_checklist=$date_close_checklist object_id=$bloc_id type='fermeture_preop'}}
{{/if}}

{{if ("dPsalleOp CDailyCheckList choose_moment_edit"|gconf && $require_check_list && $date_open_checklist|date_format:$conf.date != $date|date_format:$conf.date)
|| ($date_open_checklist|date_format:$conf.date == $date|date_format:$conf.date && "dPsalleOp CDailyCheckList multi_check_preop"|gconf)}}
  {{mb_include module=salleOp template=inc_last_valid_checklist date_checklist=$date_open_checklist object_id=$bloc_id type='ouverture_preop'}}
{{/if}}

{{assign var=use_concentrator value=false}}
{{if "patientMonitoring"|module_active && "patientMonitoring CMonitoringConcentrator active"|gconf}}
  {{assign var=use_concentrator value=true}}
{{/if}}

<script>
  submitPrepaForm = function(oFormPrepa, askPoste, stop_session, operation_id) {
    var callback = function() {
      onSubmitFormAjax(oFormPrepa, refreshTabReveil.curry('preop'));
    };

    var openPosteConcentrator = function () {
      App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function(){
        ConcentratorCommon.askPosteConcentrator(
          $V(oFormPrepa.operation_id),
          "{{$bloc_id}}",
          "preop",
          oFormPrepa,
          callback(),
          stop_session ? 0 : 1
        );
      });
    };

    {{if $use_concentrator}}
      if (stop_session) {
        App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function () {
          ConcentratorCommon.stopCurrentSession(operation_id, function () {
            openPosteConcentrator();
          });
        });
      }

      if (askPoste && !stop_session) {
        openPosteConcentrator();
      }

      if (!askPoste && !stop_session) {
        callback();
      }
    {{else}}
      callback();
    {{/if}}
  };

  Main.add(function() {
    {{if $isImedsInstalled}}
      ImedsResultsWatcher.loadResults();
    {{/if}}
  });

  orderTabpreop = function(col, way) {
    orderTabReveil(col, way, 'preop');
  };
</script>

<table class="tbl me-no-align me-small">
  <tr>
    <th>{{mb_colonne class="COperation" field="time_operation" order_col=$order_col order_way=$order_way function=orderTabpreop}}</th>
    <th>{{mb_colonne class="COperation" field="salle_id" order_col=$order_col order_way=$order_way function=orderTabpreop}}</th>
    <th>{{mb_colonne class="COperation" field="chir_id" order_col=$order_col order_way=$order_way function=orderTabpreop}}</th>
    <th class="narrow">
        {{mb_colonne class="COperation" field="_patient" order_col=$order_col order_way=$order_way function=orderTabpreop}}
    </th>
    <th class="narrow me-small-fields">
        {{me_form_field}}
          <input type="text" name="_seek_patient_preop" value="" class="seek_patient" onkeyup="seekPatient(this);" onchange="seekPatient(this);" />
        {{/me_form_field}}
    </th>    {{if "dPsalleOp SSPI_cell see_ctes"|gconf}}
      <th>{{tr}}SSPI_cell.see_ctes{{/tr}}</th>
    {{/if}}
    <th>{{mb_colonne class="COperation" field="libelle" order_col=$order_col order_way=$order_way function=orderTabpreop}}</th>
    <th>{{mb_colonne class="COperation" field="cote" order_col=$order_col order_way=$order_way function=orderTabpreop}}</th>
      {{if "dPsalleOp SSPI_cell see_type_anesth"|gconf}}
        <th>{{mb_colonne class="COperation" field="type_anesth" order_col=$order_col order_way=$order_way function=orderTabpreop}}</th>
      {{/if}}

      {{if "dPsalleOp SSPI_cell see_localisation"|gconf}}
        <th>{{tr}}SSPI.Chambre{{/tr}}</th>
      {{/if}}
    <th class="narrow">{{tr}}CTraitement-dossier_medical_id-desc{{/tr}}</th>
      {{if $isbloodSalvageInstalled}}
        <th>{{tr}}SSPI.RSPO{{/tr}}</th>
      {{/if}}
    {{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
      <th>{{tr}}CBrancardage{{/tr}}</th>
    {{/if}}
    <th>{{mb_colonne class="COperation" field="entree_bloc" order_col=$order_col order_way=$order_way function=orderTabpreop}}</th>
    <th class="narrow">{{mb_colonne class="COperation" field="debut_prepa_preop" order_col=$order_col order_way=$order_way function=orderTabpreop}}</th>
    {{if $use_poste || $use_concentrator}}
      <th>
        {{if $use_poste}}
          {{mb_colonne class="COperation" field="poste_preop_id" order_col=$order_col order_way=$order_way function=orderTabpreop}}
        {{/if}}

        {{if !$use_poste && $use_concentrator}}
          <th class="narrow">Conc.</th>
        {{/if}}
      </th>
    {{/if}}
    <th class="narrow">{{mb_colonne class="COperation" field="fin_prepa_preop" order_col=$order_col order_way=$order_way function=orderTabpreop}}</th>
    <th class="narrow"></th>
  </tr>
  {{foreach from=$listOperations item=_operation}}
    {{assign var=patient            value=$_operation->_ref_patient}}
    {{assign var=sejour_id          value=$_operation->sejour_id}}
    {{assign var=_operation_id      value=$_operation->_id}}
    {{assign var=session_monitoring value=$_operation->_active_session}}

    <tr>
      <td class="text">
        {{if $_operation->rank}}
          {{$_operation->_datetime|date_format:$conf.time}}
        {{else}}
          NP
        {{/if}}
      </td>
      <td>{{$_operation->_ref_salle->_shortview}}</td>
      <td class="text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir classe="me-wrapped"}}
      </td>
      <td class="text" colspan="2">
        {{mb_include module=system template=inc_object_notes object=$_operation}}

        <span class="{{if !$_operation->_ref_sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $_operation->_ref_sejour->septique}}septique{{/if}} CPatient-view"
              onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
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
        <button class="button soins notext me-tertiary me-small" onclick="showDossierSoins('{{$_operation->sejour_id}}','{{$_operation->_id}}');">
            {{tr}}mod-soins-tab-viewDossierSejour{{/tr}}
        </button>
          {{if $isImedsInstalled}}
            <button class="labo button notext me-tertiary me-small" onclick="showDossierSoins('{{$_operation->sejour_id}}','{{$_operation->_id}}','Imeds');">
                {{tr}}COperation-labo_anapath_id-court{{/tr}}
            </button>
          {{/if}}
        <button type="button" class="injection notext me-tertiary me-small" onclick="Operation.dossierBloc('{{$_operation->_id}}', true, 'surveillance_perop')">
            {{tr}}COperation-action-Block file{{/tr}}
        </button>
          {{mb_include module=patients template=inc_antecedents_allergies show_atcd=0 dossier_medical=$patient->_ref_dossier_medical patient_guid=$patient->_guid}}
      </td>
        {{if $isbloodSalvageInstalled}}
          <td>
            {{mb_include module=bloodSalvage template=inc_buttons_bloodSalvage operation=$_operation}}
          </td>
        {{/if}}
      {{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf}}
        <td>
          <div id="brancardage-{{$_operation->_guid}}">
              {{mb_include module=brancardage template=inc_exist_brancard colonne="demandeBrancardage"
              object=$_operation brancardage_to_load="aller"}}
          </div>
        </td>
      {{/if}}
      <td class="button me-small-fields">
        {{if $modif_operation}}
          <form name="editEntreeBloc{{$_operation_id}}" method="post" class="prepared">
            <input type="hidden" name="m" value="planningOp" />
            <input type="hidden" name="dosql" value="do_planning_aed" />
            {{mb_key object=$_operation}}
            {{if $_operation->entree_bloc}}
              {{mb_field object=$_operation field=entree_bloc form="editEntreeBloc$_operation_id" register=true onchange="submitPrepaForm(this.form);"}}
            {{else}}
              <input type="hidden" name="entree_bloc" value="now" />
              <button class="tick notext me-tertiary me-small" type="button" onclick="submitPrepaForm(this.form);">
                {{tr}}Modify{{/tr}}
              </button>
            {{/if}}
          </form>
        {{else}}
          {{mb_value object=$_operation field=entree_bloc}}
        {{/if}}
      </td>
      <td class="me-small-fields" style="text-align: center;">
          {{if $modif_operation}}
            <form name="editDebutPreopFrm{{$_operation_id}}" method="post" class="prepared">
              <input type="hidden" name="m" value="planningOp" />
              <input type="hidden" name="dosql" value="do_planning_aed" />
                {{mb_key object=$_operation}}
                {{if $_operation->debut_prepa_preop}}
                    {{mb_field object=$_operation field=debut_prepa_preop form="editDebutPreopFrm$_operation_id" register=true onchange="submitPrepaForm(this.form);"}}
                {{else}}
                  <input type="hidden" name="debut_prepa_preop" value="now" />
                  <button class="tick notext me-tertiary me-small" type="button" onclick="submitPrepaForm(this.form,true);">
                      {{tr}}Modify{{/tr}}
                  </button>
                {{/if}}
            </form>
          {{else}}
              {{mb_value object=$_operation field="debut_prepa_preop"}}
          {{/if}}

          {{mb_include module=forms template=inc_widget_ex_class_register_multiple object=$_operation cssStyle="display: inline-block;"}}
      </td>
      {{if $use_poste || $use_concentrator}}
        <td class="button me-small-fields">
          {{if $use_poste}}
            {{mb_include module=salleOp template=inc_form_toggle_poste_preop}}
          {{/if}}

          {{if $use_concentrator}}
            {{mb_include module=patientMonitoring template=inc_concentrator_session operation=$_operation type="preop" bloc_id=$bloc_id sspi_id=$sspi_id
            callback="refreshTabReveil.curry('preop')"}}
          {{/if}}
        </td>
      {{/if}}
      <td class="button me-small-fields">
        {{if $modif_operation}}
          <form name="editFinPreopFrm{{$_operation_id}}" method="post" class="prepared">
            <input type="hidden" name="m" value="planningOp" />
            <input type="hidden" name="dosql" value="do_planning_aed" />
            {{mb_key object=$_operation}}
            <input type="hidden" name="del" value="0" />
            {{if $_operation->fin_prepa_preop}}
              {{mb_field object=$_operation field=fin_prepa_preop form="editFinPreopFrm$_operation_id" register=true onchange="submitPrepaForm(this.form, true, true, '`$_operation->_id`');"}}
            {{else}}
              <input type="hidden" name="fin_prepa_preop" value="now" />
              <button class="tick notext me-tertiary me-small" type="button" onclick="submitPrepaForm(this.form, true, true, '{{$_operation->_id}}');">{{tr}}Modify{{/tr}}</button>
            {{/if}}
          </form>
        {{else}}
          {{mb_value object=$_operation field=fin_prepa_preop}}
        {{/if}}
      </td>
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

{{mb_include module=forms template=inc_widget_ex_class_register_multiple_end event_name=preop object_class="COperation"}}
