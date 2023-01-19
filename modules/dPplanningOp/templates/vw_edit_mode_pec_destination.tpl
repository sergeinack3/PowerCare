{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-{{$mode_pec_dest->_guid}}" method="post" action="?"
      onsubmit="return ParametrageMode.submitPecDestination(this, '{{$type}}');">
  {{mb_key   object=$mode_pec_dest}}
  {{mb_class object=$mode_pec_dest}}
  {{mb_field object=$mode_pec_dest field=group_id hidden=true}}
  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$mode_pec_dest}}
    <tr>
      <th>{{mb_label object=$mode_pec_dest field=code}}</th>
      <td>{{mb_field object=$mode_pec_dest field=code}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$mode_pec_dest field=libelle}}</th>
      <td>{{mb_field object=$mode_pec_dest field=libelle}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$mode_pec_dest field=actif}}</th>
      <td>{{mb_field object=$mode_pec_dest field=actif}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$mode_pec_dest field=default}}</th>
      <td>{{mb_field object=$mode_pec_dest field=default}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="submit" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
        {{if $mode_pec_dest->_id}}
          <button type="button" class="trash" onclick="ParametrageMode.deletePecDestination(this.form, '{{$type}}');">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>