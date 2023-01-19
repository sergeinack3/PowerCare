{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=stock script=return_form}}

<button class="new" onclick="ReturnForm.create()">{{tr}}CProductReturnForm-title-create{{/tr}}</button>

<table class="main tbl">
  <tr>
    <th>{{mb_title class=CProductReturnForm field=product_return_form_id}}</th>
    <th>{{mb_title class=CProductReturnForm field=datetime}}</th>
    <th>{{mb_title class=CProductReturnForm field=supplier_id}}</th>
    <th>{{mb_title class=CProductReturnForm field=status}}</th>
  </tr>

  {{foreach from=$return_forms item=_return_form}}
    <tr>
      <td>{{mb_value object=$_return_form field=product_return_form_id}}</td>
      <td>{{mb_value object=$_return_form field=datetime}}</td>
      <td>{{mb_value object=$_return_form field=supplier_id}}</td>
      <td>{{mb_value object=$_return_form field=status}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">{{tr}}CProductReturnForm.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>