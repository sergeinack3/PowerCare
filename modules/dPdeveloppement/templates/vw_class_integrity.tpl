{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th>{{mb_title class=CRefCheckTable field=class}}</th>
    <th>{{mb_title class=CRefCheckField field=field}}</th>
    <th>{{mb_title class=CRefCheckField field=target_class}}</th>
    <th>{{mb_title class=CRefCheckField field=start_date}}</th>
    <th>{{tr}}CRefCheckField-_duration{{/tr}}</th>
    <th>{{mb_title class=CRefCheckField field=last_id}}</th>
    <th colspan="2">{{tr}}CRefCheckField-Count errors{{/tr}}</th>
  </tr>

  {{foreach from=$ref_check_table->_back.ref_fields item=_ref_check_field name=ref_check_fields}}
    <tr>
      {{if $smarty.foreach.ref_check_fields.first}}
        <td rowspan="{{$ref_check_table->_back.ref_fields|@count}}">
          {{mb_value object=$ref_check_table field=class}}
        </td>
      {{/if}}

      <td>{{mb_value object=$_ref_check_field field=field}}</td>
      <td>{{mb_value object=$_ref_check_field field=target_class}}</td>
      <td>{{mb_value object=$_ref_check_field field=start_date}}</td>
      <td>{{$_ref_check_field->_duration}}</td>
      <td>{{$_ref_check_field->last_id|number_format:0:',':' '}}</td>
      <td {{if $_ref_check_field->_count_errors == 0}}colspan="2"{{/if}}>{{$_ref_check_field->_count_errors}}</td>
      {{if $_ref_check_field->_count_errors > 0}}
        <td>
          <button type="button" class="search notext compact" onclick="ReferencesCheck.displayFieldErrors('{{$_ref_check_field->_id}}');">
            {{tr}}Show{{/tr}}
          </button>
        </td>
      {{/if}}
    </tr>
  {{/foreach}}
</table>