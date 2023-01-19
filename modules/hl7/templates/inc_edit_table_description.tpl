{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hl7 script=tables_hl7v2 ajax=true}}

<form name="createHL7TabDescription" action="?m=hl7" method="post" onsubmit="return onSubmitFormAjax(this, Tables_hl7v2.loadTables);">
  <input type="hidden" name="m" value="hl7" />
  <input type="hidden" name="@class" value="{{$table_description->_class}}" />
  {{mb_key object=$table_description}}
  {{mb_field object=$table_description field="user" hidden=true}}

  <table class="form">
    <tr>
      <th class="title" colspan="2">{{tr}}CHL7v2TableDescription-title-create{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_label object=$table_description field="number"}}</th>
      <td>{{mb_field object=$table_description field="number"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$table_description field="description"}}</th>
      <td>{{mb_field object=$table_description field="description" size="50"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$table_description field="user"}}</th>
      <td>{{mb_value object=$table_description field="user"}}</td>
    </tr>

    <td colspan="2" style="text-align: center">
      <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
    </td>
  </table>
</form>