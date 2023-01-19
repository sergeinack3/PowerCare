{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $type == "transmissions"}}
  <table class="main layout">
    <tr>
      <td>
          <span class="type_item circled">
            {{tr}}CTransmissionMedicale{{/tr}}
          </span>
      </td>
    </tr>

    {{foreach from=$list item=item name=transmissions}}
      <tr>
        <td style="width: 50%;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
          </span>
          <br>
          {{mb_value object=$item field=date}}
          <br>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_user}}
        </td>
        <td>
          <span class="timeline_description"><strong>{{tr}}CTransmissionMedicale-libelle_ATC{{/tr}} :</strong> {{mb_value object=$item field=libelle_ATC}}</span>
          <span class="timeline_description"><strong>{{tr}}CTransmissionMedicale-degre{{/tr}} :</strong> {{mb_value object=$item field=degre}}</span>
          <span class="timeline_description"><strong>{{mb_value object=$item field=type}} :</strong> {{$item->text}}</span>
        </td>
      </tr>
      {{if !$smarty.foreach.transmissions.last}}
        <tr>
          <td colspan="2"><hr class="item_separator"/></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}

{{if $type == "observations"}}
  <table class="main layout">
    <tr>
      <td>
          <span class="type_item circled">
            {{tr}}CObservationMedicale{{/tr}}
          </span>
      </td>
    </tr>

    {{foreach from=$list item=item name=observations}}
      <tr>
        <td style="width: 50%;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
          </span>
          <br/>
          {{mb_value object=$item field=date}}
          <br/>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_user}}
        </td>
        <td>
          <span class="timeline_description"><strong>{{tr}}CObservationMedicale-type{{/tr}} :</strong> {{mb_value object=$item field=type}}</span>
          <span class="timeline_description"><strong>{{tr}}CObservationMedicale-degre{{/tr}} :</strong> {{mb_value object=$item field=degre}}</span>
          <span class="timeline_description"><strong>{{tr}}CTransmissionMedicale-text{{/tr}} :</strong> {{$item->text}}</span>
        </td>
      </tr>
      {{if !$smarty.foreach.observations.last}}
        <tr>
          <td colspan="2"><hr class="item_separator"/></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}

{{if $type == "score"}}
  <table class="main layout">
    {{foreach from=$list item=item name=score}}
      {{if $item->_class == 'CExamIgs'}}
        {{assign var=datetime value='date'}}
        {{assign var=score value='scoreIGS'}}
      {{else}}
        {{assign var=datetime value='datetime'}}
        {{assign var=score value='total'}}
      {{/if}}
      <tr>
        <td>
          <span class="type_item circled">
            {{tr}}{{$item->_class}}{{/tr}}
          </span>
        </td>
      </tr>
      <tr>
        <td style="width: 50%;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
          </span>
          <br>
          {{mb_value object=$item field=$datetime}}
        </td>
        <td>
          <span class="timeline_description" onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');"><strong>{{tr}}{{$item->_class}}-{{$score}}-court{{/tr}} :</strong> {{mb_value object=$item field=$score}}</span>
        </td>
      </tr>
      {{if !$smarty.foreach.score.last}}
        <tr>
          <td colspan="2"><hr class="item_separator"/></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>  
{{/if}}

{{if $type == "vitals"}}
  <table class="main layout">
    <tr>
      <td>
          <span class="type_item circled">
            {{tr}}CConstantesMedicales{{/tr}}
          </span>
      </td>
    </tr>

    {{foreach from=$list item=item name=vitals}}
      <tr>
        <td style="width: 50%;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
          </span>
          <br>
          {{mb_value object=$item field=datetime}}
          <br>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_user}}
        </td>
        <td>
          {{foreach from=$item->_valued_cst key=name item=value}}
            <span class="timeline_description" {{if isset($value.description.readonly|smarty:nodefaults)}}style="font-style: italic"{{/if}}>
              <strong>{{tr}}CConstantesMedicales-{{$name}}{{/tr}} :</strong> {{$value.value}} {{$value.description.unit}}
            </span>
          {{/foreach}}
        </td>
      </tr>
      {{if !$smarty.foreach.vitals.last}}
        <tr>
          <td colspan="2"><hr class="item_separator"/></td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}
