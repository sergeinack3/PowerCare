{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=salle_id value=""}}
{{assign var=sSalle value=$salle_id|ternary:"-s$salle_id":""}}
{{foreach from=$listHours item=_hour}}
{{foreach from=$listMins item=_min}}
  {{assign var="creneau" value="$curr_day$sSalle-$_hour:$_min:00"}}
  {{assign var=affichage value=$affichages.$key_bloc.$creneau}}
  
  {{if $affichage === "empty"}}
    {{assign var=blocage value=0}}
    {{if $_salle->_blocage.$curr_day|@count}}
      {{assign var=time value="$curr_day $_hour:$_min:00"}}
      {{foreach from=$_salle->_blocage.$curr_day item=_blocage}}
        {{if $time >= $_blocage->deb && $time <= $_blocage->fin}}
          {{assign var=blocage value=1}}
        {{/if}}
      {{/foreach}}
    {{/if}}

    <td class="empty{{if $_min == "00"}} firsthour{{/if}}{{if $blocage}} hatching{{/if}}"></td>
    {{elseif $affichage === "full"}}

    {{else}}
    {{assign var=_listPlages value=$listPlages.$curr_day.$key_bloc}}
    {{assign var=plage value=$_listPlages.$affichage}}
 
      {{if $plage->secondary_function_id && $plage->_ref_secondary_function->color }}
        {{assign var=color value=$plage->_ref_secondary_function->color}}
      {{else}}
        {{mb_ternary var=color test=$plage->chir_id value=$plage->_ref_chir->_ref_function->color other=$plage->_ref_spec->color}}
      {{/if}}
      {{if $plage->_color_status}}
        {{assign var=color value=$plage->_color_status}}
      {{/if}}

      {{assign var="pct" value=$plage->_fill_rate}}
      {{if $pct gt 100}}
      {{assign var="pct" value=100}}
      {{/if}}

      <td class="plageop me-plageop" style="background-color: #{{$color}};" colspan="{{$plage->_nbQuartHeure}}">
      <div class="me-plageop-container" style="background-color: #{{$color}};">
        {{mb_include module=system template=inc_object_notes object=$plage note_class="me-margin-top-4"}}
        <div class="progressBar" style="height: 3px;" title="{{$plage->_fill_rate}} % du temps occupé">
          <div class="bar" style="width: {{$pct}}%;height: 3px;border-right: 2px solid #000; background-color: #{{$plage->_fill_rate_color}};">
          </div>
        </div>
        {{if $bloc->_canEdit}}
          <strong title="{{$plage->_fill_rate}} % du temps occupé">
            {{mb_include module=bloc template=inc_initials_vacation_urgence}}

            <a onclick="EditPlanning.order('{{$plage->_id}}');" href="#">
              {{$plage->_view|spancate:30:"...":true}}
            </a>
            ({{$plage->_count_operations_placees}}/{{$plage->_count_operations}})
          </strong>
          <a onclick="EditPlanning.edit('{{$plage->_id}}','{{$curr_day}}');" href="#" class="not-printable">
            {{me_img src="edit.png" icon="edit" class="me-primary" height=16 width=16 title="Modifier la plage"}}
          </a>

          {{if "kereon"|module_active}}
            {{mb_script module=kereon script=kereon}}

            <a onclick="Kereon.showBoxPlot('{{$plage->_id}}');" href="#" class="not-printable">
              <img src="modules/kereon/images/icon.png" title="{{tr}}CKereonService-View optimal scheduling of interventions{{/tr}}" border="0" height="16" width="16" />
            </a>
          {{/if}}

        {{if $plage->verrouillage == "oui"}}
          <i class="me-icon lock me-error" title="Plage verrouillée" border="0" height="16" width="16" ></i>
        {{/if}}
        {{assign var=affectations value=$plage->_ref_affectations_personnel}}

        {{if ($affectations.op|@count) || ($affectations.op_panseuse|@count) || ($affectations.iade|@count) || ($affectations.manipulateur|@count) || ($affectations.sagefemme|@count)}}
          <a onclick="EditPlanning.order('{{$plage->_id}}');" href="#">
           {{me_img src="personnel.png" icon="personnel" width=16 height=16
                onmouseover="ObjectTooltip.createDOM(this, 'tooltip-content-plage-`$plage->_id`')"}}
           {{mb_include module=system template=inc_vw_counter_tip count=$plage->_count_affectations_personnel}}
          </a>
          <div id="tooltip-content-plage-{{$plage->_id}}" style="display: none; width: 200px;">
            <table class="tbl">
              {{foreach from=$affectations key=type_personnel item=_affectations}}
                {{if in_array($type_personnel, array("op", "op_panseuse", "iade", "manipulateur", "sagefemme")) && $_affectations|@count}}
                <tr>
                  <th>{{tr}}CPersonnel.emplacement.{{$type_personnel}}{{/tr}}</th>
                </tr>
                {{foreach from=$_affectations item=_affectation}}
                <tr>
                  <td class="text">
                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_affectation->_ref_personnel->_ref_user}}
                  </td>
                </tr>
                {{foreachelse}}
                <tr>
                  <td class="empty">{{tr}}None{{/tr}}</td>
                </tr>
                {{/foreach}}
                {{/if}}
              {{/foreach}}
            </table>
          </div>

        {{/if}}
        {{else}}
        <strong title="{{$plage->_fill_rate}} % du temps occupé">
          {{mb_include module=bloc template=inc_initials_vacation_urgence}}
          {{$plage->_view}}
          ({{$plage->_count_operations_placees}}/{{$plage->_count_operations}})
        </strong>
        {{/if}}
      </div>
    </td>
  {{/if}}
{{/foreach}}
{{/foreach}}
