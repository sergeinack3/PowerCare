{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=bloc template=horizontal_planning_style}}

{{assign var=tooltip_event value='onmouseover'}}
{{if @$app->user_prefs.touchscreen == 1 || $ua->device_type == 'tablet'}}
  {{assign var=tooltip_event value='onclick'}}
{{/if}}

{{assign var=check_identity_pat value="dPsalleOp COperation check_identity_pat"|gconf}}

<script type="text/javascript">
  submitTiming = function(form) {
    onSubmitFormAjax(form, function() {
      HPlanning.reloadTiming($V(form.elements['operation_id']));
    });
  };

  Main.add(function() {
    HPlanning.setContainerWidth();
  });
</script>

<div id="planning_header">
  <table class="salles_header">
    <tr>
      <th class="placeholder"></th>
    </tr>
    {{foreach from=$salles key=salle_id item=data}}
      <tr data-salle_id="{{$salle_id}}">
        <th id="CSalle-{{$salle_id}}" class="salle"
            style="height: {{math equation="x * 85" x=$data.height}}px; {{if $data.salle|instanceof:'Ox\Mediboard\Bloc\CSalle' && $data.salle->color}}background-color: #{{$data.salle->color}};{{/if}}"
          {{if $data.salle|instanceof:'Ox\Mediboard\Bloc\CSalle' && $move_operations}} ondragover="HPlanning.allowDrop(event);" ondrop="HPlanning.onDrop(event);" data-name="{{$data.salle->nom}}"{{/if}}>
          {{if is_object($data.salle)}}
            <span class="action">
              <button type="button" class="lock notext" onclick="HPlanning.editBlocage(null, '{{$salle_id}}');">
                {{tr}}CBlocage-action-new{{/tr}}
              </button>
            </span>
            {{$data.salle->_view|replace:'-':'<br>'}}
          {{else}}
            {{$data.salle}}
          {{/if}}
        </th>
      </tr>
    {{/foreach}}
  </table>
</div><div id="timeline_container">
  {{foreach from=$periods key=period_id item=period name=periods}}
    {{assign var=hour_width value=$period.hour_width}}
    {{math assign=half_hour_width equation="x/2" x=$hour_width}}
    {{assign var=half_hour_width value=$half_hour_width|round}}

    <div class="period_{{$period_id}}"  style="height: 20px;{{if $time.period != $period_id}} display: none;{{/if}}">
    {{if !$smarty.foreach.periods.first}}
      <span class="period_{{$period_id}}" style="float: left; cursor: pointer; height: 20px;{{if $time.period != $period_id}} display: none;{{/if}}" onclick="HPlanning.previousPeriod({{$period_id}});">
        {{me_img src="prev.png" icon="arrow-left" class="me-primary"}}
      </span>
    {{/if}}
    {{if !$smarty.foreach.periods.last}}
      <span class="period_{{$period_id}}" style="float: right; cursor: pointer; height: 20px;{{if $time.period != $period_id}} display: none;{{/if}}" onclick="HPlanning.nextPeriod({{$period_id}});">
        {{me_img src="next.png" icon="arrow-right" class="me-primary"}}
      </span>
    {{/if}}
    </div>

    <table class="horizontal_planning period_{{$period_id}}{{if $time.period == $period_id}} selected{{/if}}" id="period_{{$period_id}}_table" data-period_id="{{$period_id}}" {{if $time.period != $period_id}} style="display: none;"{{/if}}>
      <tr>
        {{foreach from=$period.hours item=hour}}
          <th class="hour" colspan="2" data-hour="{{$hour}}" style="max-width: {{$hour_width}}px; min-width: {{$hour_width}}px;">
            {{$hour|string_format:'%02dh00'}}
          </th>
        {{/foreach}}
      </tr>
      {{foreach from=$salles key=salle_id item=data}}
        <tr data-salle_id="{{$salle_id}}"{{if $data.salle|instanceof:'Ox\Mediboard\Bloc\CSalle' && $move_operations}} ondragover="HPlanning.allowDrop(event);" ondrop="HPlanning.onDrop(event);"{{/if}}>
          {{foreach from=$period.hours item=hour}}
            <td class="hour_first_half" style="height: {{math equation="x*85" x=$data.height}}px; min-width: {{$half_hour_width}}px; max-width: {{$half_hour_width}}px;"></td>
            <td class="hour_second_half" style="height: {{math equation="x*85" x=$data.height}}px; min-width: {{$half_hour_width}}px; max-width: {{$half_hour_width}}px;"></td>
          {{/foreach}}
        </tr>
      {{/foreach}}
    </table>

    {{if $period_id == $time.period && array_key_exists('position', $time)}}
      {{math assign=height equation="x*85+20" x=$height}}
      <div class="current_time period_{{$period_id}}" style="left: {{$time.position}}px; top: 20px; height: {{$height}}px;"></div>
    {{/if}}
  {{/foreach}}

  {{assign var=top value=-40}}
  {{assign var=top_period value=$top}}
  {{assign var=ref_top value=$top}}

  {{foreach from=$salles key=salle_id item=data}}
    {{math assign=top equation="x+85" x=$top}}
    {{assign var=ref_top value=$top}}

    {{foreach from=$data.periods key=period item=operations_by_area name=periods}}
      {{assign var=top_period value=$ref_top}}

      {{foreach from=$operations_by_area key=rank item=_operations}}
        {{if $rank}}
          {{math assign=top_period equation="x+85" x=$top_period}}
        {{/if}}
        {{foreach from=$_operations item=operation}}
          {{assign var=positions value=$operation.positions}}
          {{assign var=_operation value=$operation.object}}
          {{assign var=sejour value=$_operation->_ref_sejour}}
          {{assign var=patient value=$sejour->_ref_patient}}

          {{if $positions.width_preop}}
            <div id="preop_{{$_operation->_guid}}_{{$period}}" class="preop period_{{$period}}" style="top: {{$top_period}}px; left: {{$positions.position_preop}}px; width: {{$positions.width_preop}}px;{{if $period != $time.period}} display: none;{{/if}}"></div>
          {{/if}}
          <script>
            Main.add(function() {
              HPlanning.setEventFor('{{$_operation->_guid}}_{{$period}}');
            });
          </script>

          <div id="{{$_operation->_guid}}_{{$period}}" class="operation period_{{$period}} {{$operation.state}}{{if $positions.width_preop}} has_preop{{/if}}{{if $positions.width_postop}} has_postop{{/if}}{{if !$_operation->plageop_id}} hors_plage{{/if}}{{if $positions.width < 300}} undersized{{/if}}"
               data-operation_id="{{$_operation->_id}}"{{if !$_operation->plageop_id}} data-time_operation="{{$_operation->time_operation}}"{{/if}}{{if $move_operations}} draggable="true" ondragstart="HPlanning.onDrag(event);"{{/if}} style="top: {{$top_period}}px; left: {{$positions.position}}px; width: {{$positions.width}}px;{{if $period != $time.period}} display: none;{{else}}display: inline-block;{{/if}}">
            <div id="actions-{{$_operation->_guid}}_{{$period}}" class="operation_actions">
              {{if !$check_identity_pat}}
                <button type="button" class="fa fa-hourglass notext" onclick="HPlanning.openOperationTimings('{{$_operation->_id}}');">Timings de l'intervention</button>
              {{/if}}
              <button type="button" class="barcode notext"
                      onclick="HPlanning.searchPatientByNDA({{$app->user_prefs.auto_entree_bloc_on_pat_select}} {{if $check_identity_pat}}, '{{$sejour->_id}}', '{{$_operation->_id}}'{{/if}});">
                {{tr}}CPatient-Choose an administrative file number{{/tr}}
              </button>
              {{if !$check_identity_pat}}
                <button type="button" class="search notext" onclick="Operation.dossierBloc('{{$_operation->_id}}', HPlanning.display.curry(getForm('timeline_filters')));">
                  Dossier de bloc
                </button>
              {{/if}}
            </div>
            <span id="infos-{{$_operation->_guid}}_{{$period}}" style="display: none; float: right; padding: 2px; background-color: azure; border-radius: 2px; border: slategrey solid 1px; ">
              {{mb_include module=system template=inc_object_notes object=$_operation float=right}}
              {{if 'dPhospi prestations systeme_prestations'|gconf == 'expert' && $sejour->_liaisons_for_prestation|@count}}
                {{foreach from=$sejour->_liaisons_for_prestation item=_liaison name=prestations}}
                  {{$_liaison->_ref_item_realise->nom}}
                  {{if !$smarty.foreach.prestations.last}}
                     |
                  {{/if}}
                {{/foreach}}
              {{/if}}
              {{if 'dPplanningOp CSejour use_charge_price_indicator'|gconf && $sejour->charge_id}}
                {{if 'dPhospi prestations systeme_prestations'|gconf == 'expert' && $sejour->_liaisons_for_prestation|@count}}
                   |
                {{/if}}
                <strong>{{$sejour->_ref_charge_price_indicator}}</strong>
              {{else}}
                <strong>{{mb_value object=$sejour field=type}}</strong>
              {{/if}}
              {{assign var=dossier value=$patient->_ref_dossier_medical}}
              {{if $dossier && $dossier->_id && $dossier->_count_allergies}}
                {{me_img src="warning.png" attr="`$tooltip_event`=\"ObjectTooltip.createEx(this, '`$patient->_guid`', 'allergies');\""}}
                {{mb_include module=system template=inc_vw_counter_tip count=$dossier->_count_allergies}}
              {{elseif $dossier->_ref_allergies|@count}}
                <span class="texticon texticon-allergies-ok" title="{{tr}}CAntecedent-No known allergy-desc{{/tr}}">{{tr}}CAntecedent-Allergie|pl{{/tr}}</span>
              {{/if}}
              {{if $dossier && $dossier->_count_antecedents}}
                <span class="texticon texticon-atcd" {{$tooltip_event}}="ObjectTooltip.createEx(this, '{{$dossier->_guid}}', 'antecedents');">Atcd</span>
              {{/if}}
            </span>

            {{if $positions.width_induction}}
              <span class="induction_time" style="left: {{$positions.position_induction}}px; width: {{$positions.width_induction}}px;"></span>
            {{/if}}
            <span>
              {{$_operation->_datetime_best|date_format:$conf.time}} -
              {{if $_operation->fin_op}}
                {{$_operation->fin_op|date_format:$conf.time}}
              {{else}}
                {{$_operation->_fin_prevue|date_format:$conf.time}}
              {{/if}}<br>

              <span style="cursor: pointer;" onclick="Patient.viewModal('{{$patient->_id}}');">
                <span id="COperation-{{$_operation->_id}}-patient">{{$patient}}</span>
                {{if $sejour->_NDA}} - {{$sejour->_NDA}}{{/if}}
                 - {{mb_value object=$patient field=naissance}}
              </span><br />
              <span id="{{$_operation->_guid}}-libelle">{{$_operation->libelle}}</span> - {{$_operation->cote}}<br>

              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir}}
              {{if $_operation->_ref_anesth && $_operation->_ref_anesth->_id}}
                <br>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_anesth}}
              {{/if}}

              <br />
              <span {{$tooltip_event}}="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
                {{$sejour->_shortview}}
              </span>
            </span>
          </div>

          {{if $positions.width_postop}}
            <div id="postop_{{$_operation->_guid}}_{{$period}}" class="postop period_{{$period}}" style="top: {{$top_period}}px; left: {{$positions.position_postop}}px; width: {{$positions.width_postop}}px;{{if $period != $time.period}} display: none;{{/if}}"></div>
          {{/if}}
        {{/foreach}}
      {{/foreach}}

      {{if $top_period > $top}}
        {{assign var=top value=$top_period}}
      {{/if}}
    {{/foreach}}

    {{if $data.salle|instanceof:'Ox\Mediboard\Bloc\CSalle' && $data.salle->_blocage|@count}}
      {{foreach from=$data.salle->_blocage key=period_key item=period}}
        {{foreach from=$period item=blocage}}
          <div class="blocage period_{{$period_key}}" style="top: {{$top_period}}px; left: {{$blocage.position}}px; width: {{$blocage.width}}px;{{if $period_key != $time.period}} display: none;{{/if}}">
            <span class="view" onclick="HPlanning.editBlocage('{{$blocage.id}}');">
              {{$blocage.view}}
            </span>
          </div>
        {{/foreach}}
      {{/foreach}}
    {{/if}}
  {{/foreach}}
</div>