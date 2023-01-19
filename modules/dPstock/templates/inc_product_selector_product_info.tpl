{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th style="width: 5%;">{{mb_title object=$product field=name}}</th>
    <td style="white-space: normal;">{{mb_value object=$product field=name}}</td>
  </tr>
  <tr>
    <th>{{mb_title object=$product field=description}}</th>
    <td>{{mb_value object=$product field=description}}</td>
  </tr>
  <tr>
    <th>{{mb_title object=$product field=code}}</th>
    <td>{{mb_value object=$product field=code}}</td>
  </tr>
  <tr>
    <th colspan="2" class="title" style="font-size: 1.0em;">{{tr}}CProduct-packaging{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CProduct-_quantity{{/tr}}</th>
    <td>
      {{$product->_quantity}}
      <input name="_unit_quantity" type="hidden" value="{{$product->_unit_quantity}}" />
      <input name="_unit_title" type="hidden" value="{{$product->_unit_title}}" />
      <input name="packaging" type="hidden" value="{{$product->packaging}}" />
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$product field="packaging"}}</th>
    <td>{{mb_value object=$product field="packaging"}}</td>
  </tr>
  <tr>
    <th colspan="2" class="title" style="font-size: 1em;">{{tr}}CProductStockGroup{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CProductStockGroup-quantity{{/tr}}</th>
    <td>
      {{if $product->_ref_stock_group->_id}}
        {{$product->_ref_stock_group->quantity}}
        {{mb_include module=stock template=inc_bargraph stock=$product->_ref_stock_group}}
      {{else}}
        {{tr}}CProductStockGroup.none{{/tr}}
      {{/if}}
    </td>
  </tr>
</table>