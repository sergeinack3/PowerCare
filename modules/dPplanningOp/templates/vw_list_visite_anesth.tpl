{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th>{{tr}}CConsultAnesth-chir_id{{/tr}}</th>
    <th colspan="2">{{tr}}CPatient{{/tr}}</th>
    <th>{{tr}}COperation{{/tr}}</th>
    <th>{{tr}}CConsultation-heure{{/tr}}</th>
    <th>{{tr}}CChambre{{/tr}}</th>
    <th>{{tr}}CConsultation{{/tr}}</th>
    <th>{{tr}}COperation-anesth_id-court{{/tr}}</th>
    <th>{{tr}}CConsultAnesth-type_anesth-court{{/tr}}</th>
    <th>{{tr}}CConsultAnesth-ASA{{/tr}}</th>
    <th colspan="2">Visite</th>
    <th colspan="2">Dossiers</th>
  </tr>
  {{foreach from=$allIntervByService key=_key_service item=_list_intervs}}
    <tr>
      {{if $_key_service == "non_place"}}
        <th colspan="14" class="section">Non placés</th>
      {{else}}
        <th colspan="14" class="section">{{tr}}CService{{/tr}} {{$services.$_key_service}}</th>
      {{/if}}
    </tr>
    {{foreach from=$_list_intervs item=_operation}}
      <tbody id="visite_anesth_{{$_operation->_guid}}">
        {{mb_include module=planningOp template=inc_list_visite_anesth}}
      </tbody>
    {{/foreach}}
  {{foreachelse}}
    <tr>
      <td colspan="14" class="empty">{{tr}}COperation.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>