{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=maternite  script=placement ajax=1}}

{{assign var=curr_affectation value=$sejour->_ref_curr_affectation}}
{{assign var=lit              value=$curr_affectation->_ref_lit}}
{{assign var=chambre          value=$lit->_ref_chambre}}
{{assign var=service          value=$curr_affectation->_ref_service}}
{{assign var=last_operation   value=$sejour->_ref_last_operation}}
{{assign var=salle            value=$last_operation->_ref_salle}}
{{assign var=bloc             value=$salle->_ref_bloc}}
{{assign var=no_affectation   value=0}}

{{if $curr_affectation && !$curr_affectation->_id}}
    {{assign var=no_affectation value=1}}
{{/if}}

{{* Move to another service and bed *}}
<form name="{{$sejour->_guid}}_move_service" method="post">
  <input type="hidden" name="m" value="hospi"/>
  <input type="hidden" name="dosql" value="do_affectation_split"/>
  <input type="hidden" name="_date_split" value="now"/>
  <input type="hidden" name="affectation_id" value="{{$curr_affectation->_id}}"/>
  <input type="hidden" name="_service_id"/>
  <input type="hidden" name="_new_lit_id"/>
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}"/>
  <input type="hidden" name="entree" value="{{$curr_affectation->entree}}"/>
  <input type="hidden" name="sortie" value="{{$curr_affectation->sortie}}"/>
  <input type="hidden" name="_mod_mater" value="0"/>
  <input type="hidden" name="effectue" value="1"/>
  <input type="hidden" name="no_synchro" value="1"/>
  <input type="hidden" name="redirect" value="redirect"/> {{* Faux redirect pour que le callback ci-dessous puisse s'exécuter *}}
  <input type="hidden" name="callback" value="Placement.mapAffectation"/>
</form>

{{* Move to another bloc and room *}}
<form name="{{$sejour->_guid}}_move_bloc" method="post">
  {{mb_key   object=$last_operation}}
  {{mb_class object=$last_operation}}
  <input type="hidden" name="_bloc_id" />
  <input type="hidden" name="salle_id" value="{{$last_operation->salle_id}}" />
  <input type="hidden" name="redirect" value="redirect" /> {{* Faux redirect pour que le callback ci-dessous puisse s'exécuter *}}
  <input type="hidden" name="callback" value="Placement.mapAffectation" />
</form>

<!-- Service change form for a corridor assignment or return in the bed -->
<form name="changeServiceForm" method="post">
  <input type="hidden" name="m" value="hospi" />
  <input type="hidden" name="dosql" value="do_affectation_aed" />
  <input type="hidden" name="affectation_id" value="{{$curr_affectation->_id}}" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="service_id" value="{{$service->_id}}"/>
  <input type="hidden" name="lit_id" value="{{$lit->_id}}"/>
  <input type="hidden" name="entree" value="{{$curr_affectation->entree}}"/>
  <input type="hidden" name="sortie" value="{{$curr_affectation->sortie}}"/>
  <input type="hidden" name="_mod_mater" value="0"/>
</form>

<form name="choice_lit_or_room" method="post" onsubmit="">
  <input type="hidden" name="_current_service_id" value="{{$service->_id}}"/>
  <input type="hidden" name="_current_bloc_id" value="{{if $bloc && $bloc->_id}}{{$bloc->_id}}{{/if}}"/>
  <input type="hidden" name="_service_id"/>
  <input type="hidden" name="_lit_id"/>
  <input type="hidden" name="_bloc_id"/>
  <input type="hidden" name="_salle_id"/>
  <input type="hidden" name="_entree_salle" value="{{$last_operation->entree_salle}}"/>
  <input type="hidden" name="_sejour_entree" value="{{$sejour->entree}}"/>
  <input type="hidden" name="_sejour_sortie" value="{{$sejour->sortie}}"/>
  <input type="hidden" name="_mod_mater" value="1"/>

  <table class="main form">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CAffectation-Patient movement{{/tr}} : {{$sejour->_ref_patient->_view}}
        <br/>
        <span style="font-size: 0.8em;">
          {{tr}}CAffectation-Current location{{/tr}} :
            {{$service->_view}}
            {{if $chambre && $chambre->_id}}
              &rarr; {{$chambre->_view}}
            {{/if}}
            {{if $lit && $lit->_id}}
              &rarr; {{$lit->_view}}
            {{/if}}
            {{if !$lit->_id}}
              &rarr; {{tr}}CAffectation-Corridor{{/tr}}
            {{/if}}
        </span>
      </th>
    </tr>
    <tr>
      <td class="halfPane">
        <fieldset id="container_service">
          <legend>
            <input type="radio" name="group_by_prat" onclick="Placement.selectContextToMove(this, 'container_salle');" checked/>
            {{tr}}CService{{/tr}}
          </legend>
          <table>
            {{foreach from=$services item=_service}}
              {{assign var=chambres value=$_service->_ref_chambres}}
              <tr>
                <td>{{mb_value object=$_service field=nom}}</td>
                <td>
                  {{if $chambres|@count >= 1}}
                    <select class="service lit_id_{{$_service->_id}}" name="lit_id_{{$_service->_id}}" onchange="Placement.getDataSelected(this, '{{$_service->_id}}', 'container_service');">
                      <option value="">&mdash; Choisir un lit</option>
                      {{foreach from=$chambres item=_chambre}}
                        {{assign var=lits value=$_chambre->_ref_lits}}
                        <optgroup label="{{$_chambre}}">
                          {{foreach from=$lits item=_lit}}
                            <option value="{{$_lit->_id}}"
                                    {{if $lit->_id == $_lit->_id}}disabled{{/if}}
                                    {{if $_lit->_occupe}}style="background-color: {{if $IS_MEDIBOARD_EXT_DARK}}#756214{{else}}#fc0{{/if}}"{{/if}}>
                              {{$_lit->nom}}
                            </option>
                          {{/foreach}}
                        </optgroup>
                      {{/foreach}}
                    </select>
                  {{else}}
                    <span class="empty" style="color: #5f5f5f;">{{tr}}CLit.none{{/tr}}</span>
                  {{/if}}
                </td>
              </tr>
            {{/foreach}}
          </table>
        </fieldset>
      </td>
      <td class="halfPane">
        <fieldset id="container_salle" disabled style="opacity: 0.5;">
          <legend>
            <input type="radio" name="group_by_prat" value="0" onclick="Placement.selectContextToMove(this, 'container_service');"/>
            {{tr}}CBlocOperatoire{{/tr}}
          </legend>

          <table>
            {{foreach from=$blocs item=_bloc}}
              <tr>
                <td>{{mb_value object=$_bloc field=nom}}</td>
                <td>
                  {{if $_bloc->_ref_salles|@count > 1}}
                    <select class="salle salle_id_{{$_bloc->_id}}" name="salle_id_{{$_bloc->_id}}" onchange="Placement.getDataSelected(this, '{{$_bloc->_id}}', 'container_salle');">
                      {{if $_bloc->_ref_salles|@count > 1}}
                        <option value="">&mdash; Choisir une salle</option>
                      {{/if}}
                      {{foreach from=$_bloc->_ref_salles item=_salle}}
                        <option value="{{$_salle->_id}}" {{if $_salle->_id == $salle->_id}}disabled{{/if}}>
                          {{$_salle->nom}}
                        </option>
                      {{/foreach}}
                    </select>
                  {{else}}
                    <span class="empty" style="color: #5f5f5f;">{{tr}}CSalle.none{{/tr}}</span>
                  {{/if}}
                </td>
              </tr>
            {{/foreach}}
          </table>
        </fieldset>
      </td>
    </tr>
    <tr id="msg_bed_locked" style="display: none;">
      <td class="button" colspan="2">
        <div style="padding: 5px;">
          {{tr}}CAffectation-Lock the bed when moving to an operating room{{/tr}}
          <input type="checkbox" name="_bed_locked" {{if !$chambre->is_examination_room}}checked{{/if}}/>
        </div>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="tick" onclick="Placement.submitLitOrSalle(this.form, '{{$sejour->_guid}}', '{{$location}}', '{{$no_affectation}}');">
          {{tr}}Move{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
