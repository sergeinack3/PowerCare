{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=form}}

<form name="{{$form}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_key object=$host_field}}
  {{mb_class object=$host_field}}

  <input type="hidden" name="del" value="" />

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$host_field colspan=2}}

    <tr>
      <th>{{mb_label object=$host_field field=display_in_tab}}</th>
      <td>{{mb_field object=$host_field field=display_in_tab typeEnum='checkbox'}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$host_field field=tab_index}}</th>
      <td>{{mb_field object=$host_field field=tab_index form=$form increment=true size=3}}</td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="save">{{tr}}common-action-Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>