{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-{{$position->_guid}}" action="?" method="post" onsubmit="return Position.submit(this);">
  {{mb_key    object=$position}}
  {{mb_class  object=$position}}
  <input type="hidden" name="del" value="0"/>
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$position}}
    <tr>
      <th>{{mb_label object=$position field=group_id}}</th>
      <td>{{mb_field object=$position field=group_id options=$groups choose="CGroups.all"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$position field=code}}</th>
      <td>{{mb_field object=$position field=code}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$position field=libelle}}</th>
      <td>{{mb_field object=$position field=libelle}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$position field=actif}}</th>
      <td>{{mb_field object=$position field=actif}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $position->_id}}
          <button class="submit" type="button" onclick="Position.submit(this.form);">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="Position.confirmDeletion(this.form);">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="button" onclick="Position.submit(this.form);">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>