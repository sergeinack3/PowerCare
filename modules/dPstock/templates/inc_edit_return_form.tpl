{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-return-form" method="post" action="?m={{$m}}&tab={{$tab}}" onsubmit="return checkOutflow(this)">
  {{mb_class object=$return_form}}
  {{mb_key object=$return_form}}

  <input type="hidden" name="date_dispensation" value="now" />
  <input type="hidden" name="manual" value="1" />
  <input type="hidden" name="cancelled" value="0" />

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$return_form colspan=4}}

    <tr>
      <th>{{mb_label object=$return_form field=datetime}}</th>
      <td>{{mb_field object=$return_form field=datetime form="edit-return-form" register=true}}</td>

      <th>{{mb_label object=$return_form field=status}}</th>
      <td>{{mb_field object=$return_form field=status}}</td>
    </tr>

    <tr>
      <td colspan="4" style="text-align: center">
        <button class="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>

    <tr>
      <th colspan="4" class="category">
        {{tr}}CProductReturnForm-back-product_outputs{{/tr}}
      </th>
    </tr>

    <tr>
      <td colspan="4">
        <table class="main tbl">
          <tr>
            <th>{{mb_title class=CProductOutput field=stock_id}}</th>
            <th>{{mb_title class=CProductOutput field=quantity}}</th>
            <th>{{mb_title class=CProductOutput field=unit_price}}</th>
          </tr>

          {{foreach from=$return_form->_ref_outputs item=_output}}
            <tr>
              <td>{{mb_title class=CProductOutput field=stock_id}}</td>
              <td>{{mb_title class=CProductOutput field=quantity}}</td>
              <td>{{mb_title class=CProductOutput field=unit_price}}</td>
            </tr>
          {{/foreach}}
        </table>
      </td>
    </tr>

  </table>
</form>



