{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{*
  @todo: synchronisation du praticien entre les 3 formulaires (mais les champs praticiens de la consult et de l'interv ne modifient pas celui du séjour
  @todo: synchronisation du patient et de la grossesse entre le sejour et la consult (et mise à jour du champ ALD si besoin)
  @todo: synchronisation du champs adresse par entre le séjour et la consultation
*}}

{{mb_script module=planningOp script=DHE            ajax=true}}
{{mb_script module=patients   script=medecin        ajax=true}}
{{mb_script module=planningOp script=prestations    ajax=true}}
{{mb_script module=cabinet    script=plage_selector ajax=true}}
{{mb_script module=planningOp script=Consultation   ajax=true}}
{{mb_script module=planningOp script=operation2     ajax=true}}
{{mb_script module=planningOp script=ccam_selector  ajax=true}}
{{mb_script module=planningOp script=plage_selector ajax=true}}
{{mb_script module=patients   script=pat_selector   ajax=true}}
{{mb_script module=planningOp script=protocole_selector ajax=true}}

{{if $sejour->_id}}
  {{if "dPmedicament"|module_active}}
    {{mb_script module=medicament script=medicament_selector}}
    {{mb_script module=medicament script=equivalent_selector}}
  {{/if}}

  {{if "dPprescription"|module_active}}
    {{mb_script module=prescription script=element_selector}}
    {{mb_script module=prescription script=prescription}}
  {{/if}}
{{/if}}

<script type="text/javascript">
  Main.add(function() {
    DHE.initialize({
      currency: '{{$conf.currency_symbol|smarty:nodefaults}}',
      sejour: {
        provenance_transfert_obligatory : '{{'dPadmissions admission provenance_transfert_obligatory'|gconf}}',
        date_entree_transfert_obligatory: '{{'dPadmissions admission date_entree_transfert_obligatory'|gconf}}',
        required_dest_when_transfert    : '{{'dPplanningOp CSejour required_dest_when_transfert'|gconf}}',
        required_dest_when_mutation     : '{{'dPplanningOp CSejour required_dest_when_mutation'|gconf}}',
        required_uf_soins               : '{{'dPplanningOp CSejour required_uf_soins'|gconf}}',
        required_uf_med                 : '{{'dPplanningOp CSejour required_uf_med'|gconf}}',
        heure_sortie_ambu               : {{'dPplanningOp CSejour default_hours heure_sortie_ambu'|gconf}},
        heure_sortie_autre              : {{'dPplanningOp CSejour default_hours heure_sortie_autre'|gconf}},
        heure_entree_veille             : {{'dPplanningOp CSejour default_hours heure_entree_veille'|gconf}},
        heure_entree_jour               : {{'dPplanningOp CSejour default_hours heure_entree_jour'|gconf}},
        blocage_occupation              : {{if $conf.dPplanningOp.CSejour.blocage_occupation == '1' && !@$modules.dPplanningOp->_can->edit}}true{{else}}false{{/if}}
      },
      operation: {
        date_min                        : '{{$date_min}}',
        date_max                        : '{{$date_max}}',
        filter_dates                    : {{if !$can->admin && !@$modules.dPbloc->_can->edit}}true{{else}}false{{/if}},
        show_duree_preop                : '{{$conf.dPplanningOp.COperation.show_duree_preop}}',
        show_presence_op                : '{{$conf.dPplanningOp.COperation.show_presence_op}}',
        duree_deb                       : '{{$conf.dPplanningOp.COperation.duree_deb}}',
        duree_fin                       : '{{$conf.dPplanningOp.COperation.duree_fin}}',
        hour_urgence_deb                : '{{$conf.dPplanningOp.COperation.hour_urgence_deb}}',
        hour_urgence_fin                : '{{$conf.dPplanningOp.COperation.hour_urgence_fin}}',
        min_intervalle                  : '{{$conf.dPplanningOp.COperation.min_intervalle}}',
        time_urgence                    : '{{$operation->_time_urgence}}'
      }
    }, '{{$action}}');

    {{if $operation->_id && $operation->annulee}}
      DHE.operation.displayCancelFlag();
    {{elseif $consult->_id && $consult->annule}}
      DHE.consult.displayCancelFlag();
    {{/if}}
  });
</script>

<form name="patientEdit" method="post" action="#" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="dPpatients">
  <input type="hidden" name="dosql" value="do_patient_aed">
  <input type="hidden" name="patient_id" value="{{$patient->_id}}">
  {{mb_field object=$patient field=ald hidden=true}}
  {{mb_field object=$patient field=c2s hidden=true}}
  {{mb_field object=$patient field=acs hidden=true}}
  {{mb_field object=$patient field=acs_type hidden=true}}
  {{mb_field object=$patient field=tutelle hidden=true}}
</form>

<table class="main layout">
  <td class="halfPane">
    <div id="dhe_header" style="text-align: center; vertical-align: middle;">
      <div id="patient_icon" style="display: inline-block;"></div>
      <div style="display: inline-block;">
        <div style="text-align: left;">
          <form name="selectProtocole" action="?" method="post" onsubmit="return false;">
            <input type="text" name="_protocole_view" value="{{$protocole}}" placeholder="{{tr}}Search{{/tr}} {{tr}}CProtocole{{/tr}}"
                   onblur="" style="width: 16em;">
            <input type="hidden" name="protocole_id" value="{{$protocole->_id}}" onchange="DHE.applyProtocol(this.value);">

            <button type="button" class="search notext me-tertiary" onclick="ProtocoleSelector.init();"></button>
          </form>
        </div>

        <div style="text-align: left;">
          <form name="selectPatient" action="?" method="post" onsubmit="return false;">
            <input type="hidden" name="_patient_sexe" value="{{$patient->sexe}}">
            <input type="hidden" name="_patient_ald" value="{{$patient->ald}}" onchange="DHE.syncALD(this);">
            <input type="hidden" name="_patient_tutelle" value="{{$patient->tutelle}}">
            <input type="text" name="_patient_view" value="{{$patient}}" placeholder="{{tr}}Search{{/tr}} {{tr}}CPatient{{/tr}}"
                   style="width: 16em;">
            <input type="hidden" name="patient_id" value="{{$patient->_id}}"
                   onchange="DHE.syncPatient('summary', this);">
            <button type="button" class="cancel notext me-tertiary me-dark" onclick="DHE.emptyPatient();">{{tr}}Empty{{/tr}}</button>
            <button type="button" class="search notext me-tertiary" onclick="DHE.selectPatient();">Choisir un patient</button>
          </form>
        </div>
      </div>
    </div>
  </td>
  <td class="halfPane" id="events_selector">
    {{if $sejour->_id}}
      {{assign var=count_operations value=$sejour->_ref_operations|@count}}
      <select id="select-object-COperation" style="width: 20em;" onchange="DHE.selectObject('COperation', $V(this), this);">
        <option value="0">
          &mdash;
          {{if $count_operations}}
            {{$count_operations}} {{tr}}COperation{{if $count_operations}}|pl{{/if}}{{/tr}}
          {{else}}
            {{tr}}COperation.none{{/tr}}
          {{/if}}
        </option>
        {{foreach from=$sejour->_ref_operations item=_operation}}
          <option value="{{$_operation->_id}}"{{if $operation->_id == $_operation->_id}} selected{{/if}}{{if $_operation->annulee}} style="background-color: indianred;"{{/if}}>
            {{$_operation}}{{if $_operation->annulee}} &mdash; annulée{{/if}}
          </option>
        {{/foreach}}
      </select>
    {{/if}}
    <button type="button" id="btn_add_interv" class="new me-primary me-margin-4" onclick="DHE.selectObject('COperation', 0);">Ajouter une intervention</button>
    <br>
    {{if $sejour->_id}}
      {{assign var=count_consultations value=$sejour->_ref_consultations|@count}}
      <select id="select-object-CConsultation" style="width: 20em;" onchange="DHE.selectObject('CConsultation', $V(this), this);">
        <option value="0">
          &mdash;
          {{if $count_consultations}}
            {{$count_consultations}} {{tr}}CConsultation{{if $count_consultations > 1}}|pl{{/if}}{{/tr}}
          {{else}}
            {{tr}}CConsultation.none{{/tr}}
          {{/if}}
        </option>
        {{foreach from=$sejour->_ref_consultations item=_consultation}}
          <option value="{{$_consultation->_id}}"{{if $consult->_id == $_consultation->_id}} selected{{/if}}{{if $_consultation->annule}} style="background-color: indianred;"{{/if}}>
            {{$_consultation}}{{if $_consultation->annule}} &mdash; annulée{{/if}}
          </option>
        {{/foreach}}
      </select>
    {{/if}}
    <button type="button" id="btn_add_consult" class="new me-primary me-margin-4" onclick="DHE.selectObject('CConsultation', 0);">
      {{tr}}CConsultation-add{{/tr}}
    </button>
  </td>
</table>

<div id="dhe_body">
  <div id="dhe_sejour" style="width: 49%; display: inline-block; position: relative;">
    {{mb_include module=planningOp template=dhe/inc_sejour_summary}}
    {{mb_include module=planningOp template=dhe/inc_sejour_edit}}
  </div>
  <div id="dhe_linked_objects" style="width: 49%; display: inline-block; position: absolute;">
    <div id="objects-state">

    </div>

    <fieldset id="fieldset-objects" style="min-height: 400px;{{if $action == 'new_sejour' || $action == 'edit_sejour'}} display: none;{{/if}}">
      <legend>
        <span id="selected-object-type">
          {{if $action == 'edit_operation'}}
            {{$operation}}
          {{elseif $action == 'new_operation'}}
            {{tr}}COperation{{/tr}}
          {{elseif $action == 'new_consultation'}}
            {{tr}}CConsultation{{/tr}}
          {{elseif $action == 'edit_consultation'}}
            {{$consult}}
          {{/if}}
        </span>
        <button type="button" class="cancel notext me-tertiary me-dark" onclick="DHE.hideObjects();">Cacher le volet</button>
      </legend>
      <div id="operation"{{if $action != 'new_operation' && $action != 'edit_operation'}} style="display: none;"{{/if}}>
        {{mb_include module=planningOp template=dhe/inc_operation_summary}}
        {{mb_include module=planningOp template=dhe/inc_operation_edit}}
      </div>
      <div id="consultation"{{if $action != 'new_consultation' && $action != 'edit_consultation'}} style="display: none;"{{/if}}>
        {{mb_include module=planningOp template=dhe/inc_consultation_summary}}
        {{mb_include module=planningOp template=dhe/inc_consultation_edit}}
      </div>
    </fieldset>
  </div>
</div>

<div id="dhe_footer" style="text-align: center;">
  <form name="DHEedit" method="post" action="?" onsubmit="">
    <input type="hidden" name="m" value="planningOp">
    <input type="hidden" name="dosql" value="do_dhe_aed">
    <input type="hidden" name="del" value="0">
    {{if $modal}}
      <input type="hidden" name="postRedirect" value="m=planningOp&tab=vw_dhe">
    {{/if}}

    <input type="hidden" name="action" value="store">
    <input type="hidden" name="data" value="">

    {{if $sejour->_id}}
      <button type="button" class="save me-primary" onclick="DHE.submit('save');">{{tr}}Save{{/tr}}</button>
      <button type="button" class="cancel me-secondary" onclick="DHE.showObjectSelector('cancel');">{{tr}}Cancel{{/tr}}</button>
      {{if !$conf.dPplanningOp.CSejour.delete_only_admin || $can->admin}}
        <button type="button" class="trash me-tertiary" onclick="DHE.showObjectSelector('delete');">{{tr}}Delete{{/tr}}</button>
      {{/if}}
    {{else}}
      <button type="button" class="save me-primary" onclick="DHE.submit('save');">{{tr}}Create{{/tr}}</button>
    {{/if}}
  </form>
</div>

{{if !$conf.dPplanningOp.CSejour.delete_only_admin || $can->admin}}
  <div id="delete-objects" style="display: none;">
    {{mb_include module=planningOp template=dhe/inc_select_objects method='delete'}}
  </div>
{{/if}}

<div id="cancel-objects" style="display: none;">
  {{mb_include module=planningOp template=dhe/inc_select_objects method='cancel'}}
</div>
