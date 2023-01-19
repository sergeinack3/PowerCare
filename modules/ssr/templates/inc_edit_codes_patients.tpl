{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="3">
      {{tr}}SSR-Spreading of patient codes{{/tr}}
    </th>
  </tr>
  {{foreach from=$evenements item=_evenement name=loop_evts}}
    {{assign var=_evenement_ssr_id value=$_evenement->_id}}
    <tbody id="{{$_evenement->_guid}}-container">
      {{mb_include module=ssr template=inc_edit_codes_patients_line actes=$codes_by_evt_and_type.$_evenement_ssr_id}}
    </tbody>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="3">
        {{tr}}SSR-Spreading-only_one_patient{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>

{{foreach from=$types_acte key=_type_acte item=_type_object}}
  <form name="editPatientCodeLine{{$_type_acte}}" method="post" style="display:none"
        onsubmit="return onSubmitFormAjax(this, {onComplete:Control.Modal.refresh})">
    {{mb_class object=$_type_object}}
    {{mb_key object=$_type_object}}
    <input type="hidden" name="evenement_ssr_id" value="" />
    <input type="hidden" name="code" value="" />
    <input type="hidden" name="type" value="presta_ssr" />
  </form>
{{/foreach}}