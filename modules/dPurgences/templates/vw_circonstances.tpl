{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=urgences script=CCirconstance ajax=1}}

<button class="new" type="button" onclick="CCirconstance.edit('0')">{{tr}}New-female{{/tr}} {{tr}}CCirconstance{{/tr}}</button>
<table class="tbl">
  <tr>
    <th class="title" colspan="3">
      {{tr}}CCirconstance-list{{/tr}}
    </th>
  </tr>
  <tr>
    <th>
      {{tr}}CCirconstance-code{{/tr}}
    </th>
    <th>
      {{tr}}CCirconstance-libelle{{/tr}}
    </th>
    <th>
      {{tr}}CCirconstance-Actif{{/tr}}
    </th>
  </tr>
  {{foreach from=$list_circonstances item=_circonstance}}
    <tr>
      <td>
        <button class="edit notext" type="button" onclick="CCirconstance.edit('{{$_circonstance->_id}}')">
          {{tr}}Modify{{/tr}}
        </button>
        {{$_circonstance->code}}
      </td>
      <td>
        {{$_circonstance->libelle}}
      </td>
      <td>
        <form name="editCirc{{$_circonstance->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
          {{mb_key object=$_circonstance}}
          {{mb_class object=$_circonstance}}
          {{mb_field object=$_circonstance field="actif" onchange=this.form.onsubmit()}}
        </form>
      </td>
    </tr>
  {{/foreach}}
</table>