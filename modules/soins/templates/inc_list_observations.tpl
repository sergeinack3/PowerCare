{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=readonly value=""}}

<button class="add" onclick="Soins.addObservation('{{$sejour->_id}}', '{{$app->user_id}}');">{{tr}}CObservationMedicale-add{{/tr}}</button>

<table class="tbl">
  <tr>
    <th class="title" colspan="7">
      <button type="button" class="search me-dark" onclick="Modal.open('legend_suivi')" style="float: right;">{{tr}}Legend{{/tr}}</button>
      <select onchange="Soins.loadObservations('{{$sejour->_id}}', this.value);" style="float: right;">
        <option value="">{{tr}}All{{/tr}}</option>
        <option value="synthese" {{if $type == "synthese"}}selected{{/if}}>{{tr}}CObservationMedicale.type.synthese{{/tr}}</option>
        <option value="communication" {{if $type == "communication"}}selected{{/if}}>{{tr}}CObservationMedicale.type.communication{{/tr}}</option>
      </select>

      <select name="function_id" onchange="Soins.loadObservations('{{$sejour->_id}}', null, null, this.value);" style="float: right;">
        <option value="">{{tr}}CFunctions.all{{/tr}}</option>
        {{foreach from=$functions item=_function}}
          <option value="{{$_function->_id}}" {{if $_function->_id == $function_id}}selected{{/if}}>{{$_function->_view}}</option>
        {{/foreach}}
      </select>

      <select name="other_sejour_id" onchange="Soins.loadObservations('{{$sejour->_id}}', null, this.value);">
        <option value="all" {{if $other_sejour_id === "all"}}selected{{/if}}>
          {{if $other_sejour_id === "all"}}&rArr;{{/if}} {{tr}}soins-All contexts{{/tr}}
        </option>

        {{foreach from=$sejours_context item=_sejour}}
          <option value="{{$_sejour->_id}}"
                  {{if ($other_sejour_id && $_sejour->_id === $other_sejour_id)
                  || (!$other_sejour_id && $_sejour->_id === $sejour->_id)}}selected{{/if}}>
            {{if ($other_sejour_id && $_sejour->_id === $other_sejour_id)
            || (!$other_sejour_id && $_sejour->_id === $sejour->_id)}}&rArr;{{/if}} {{$_sejour}}
          </option>
        {{/foreach}}
      </select>
    </th>
  </tr>
  <tr>
    <th class="narrow">{{tr}}CObservationMedicale-type-court{{/tr}}</th>
    <th>{{tr}}User{{/tr}} / {{tr}}Date{{/tr}}</th>
    <th>{{mb_title class=CObservationMedicale field=object_class}}</th>
    <th colspan="3" style="width: 75%">{{mb_title class=CObservationMedicale field=text}}</th>
    <th></th>
  </tr>
  {{foreach from=$list_observations item=_suivi}}
  <tr class="{{$_suivi->_guid}}
             {{if $_suivi|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
               consultation_entree
             {{else}}
               {{if $_suivi->cancellation_date}}hatching{{/if}}
               {{if $_suivi->degre == "info"}}
                 observation_info
               {{elseif $_suivi->degre == "high"}}
                 observation_urgente
               {{/if}}
             {{/if}}">
    {{mb_include module=hospi template=inc_line_suivi}}
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="7" class="empty">{{tr}}CObservationMedicale.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>
