{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=use_meff value=1}}

{{if $use_meff}}
    {{me_form_field nb_cells=4 mb_object=$object mb_field="adresse_par_prat_id"}}

      {{mb_script module=patients script=correspondant ajax=$ajax}}

      <select name="_correspondants_medicaux" style="width: 15em;"
              onchange="$V(this.form.adresse_par_prat_id, $V(this)); $('_adresse_par_prat').hide();">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$correspondantsMedicaux key=type_correspondant item=_correspondant}}
              {{if $type_correspondant == "traitant"}}
                <option value="{{$_correspondant->_id}}" {{if $_correspondant->_id == $object->adresse_par_prat_id}}selected{{/if}}>
                    {{tr}}CPatient-medecin-Trait-court{{/tr}} : {{$_correspondant->nom}}
                </option>
              {{else}}
                  {{foreach from=$_correspondant item=medecin_corres}}
                    <option value="{{$medecin_corres->_id}}" {{if $medecin_corres->_id == $object->adresse_par_prat_id}}selected{{/if}}>
                        {{tr}}CCorrespondantPatient-court{{/tr}} : {{$medecin_corres->nom}}
                    </option>
                  {{/foreach}}
              {{/if}}
          {{/foreach}}
      </select>
      <button class="search me-tertiary" type="button" onclick="Medecin.edit(this.form)">{{tr}}common-Other|pl{{/tr}}</button>
    {{/me_form_field}}
{{else}}
  <th>{{mb_label object=$object field=adresse_par_prat_id}}</th>
  <td colspan="3">
    {{mb_script module=patients script=correspondant ajax=$ajax}}

    <select name="_correspondants_medicaux" style="width: 15em;"
            onchange="$V(this.form.adresse_par_prat_id, $V(this)); $('_adresse_par_prat').hide();">
      <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
        {{foreach from=$correspondantsMedicaux key=type_correspondant item=_correspondant}}
            {{if $type_correspondant == "traitant"}}
              <option value="{{$_correspondant->_id}}" {{if $_correspondant->_id == $object->adresse_par_prat_id}}selected{{/if}}>
                  {{tr}}CPatient-medecin-Trait-court{{/tr}} : {{$_correspondant->nom}}
              </option>
            {{else}}
                {{foreach from=$_correspondant item=medecin_corres}}
                  <option value="{{$medecin_corres->_id}}" {{if $medecin_corres->_id == $object->adresse_par_prat_id}}selected{{/if}}>
                      {{tr}}CCorrespondantPatient-court{{/tr}} : {{$medecin_corres->nom}}
                  </option>
                {{/foreach}}
            {{/if}}
        {{/foreach}}
    </select>
    <button class="search me-tertiary" type="button" onclick="Medecin.edit(this.form)">{{tr}}common-Other|pl{{/tr}}</button>
  </td>
{{/if}}
