{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-CInfoChecklist" action="" method="post" onsubmit="return InfoChecklist.submit(this);">
  {{mb_key object=$info}}
  {{mb_class object=$info}}
  {{mb_field object=$info field=group_id hidden=true}}
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$info}}
    <tr>
      <th>{{mb_label object=$info field=function_id}}</th>
      <td>
        <select name="function_id" style="width: 180px;">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_function list=$functions selected=$info->function_id}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$info field=libelle}}</th>
      <td>{{mb_field object=$info field=libelle}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$info field=actif}}</th>
      <td>{{mb_field object=$info field=actif}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $info->_id}}
          <button class="submit" type="button" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="InfoChecklist.confirmDeletion(this.form);">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="button" onclick="this.form.onsubmit();">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>