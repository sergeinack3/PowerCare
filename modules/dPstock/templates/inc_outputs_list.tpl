{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="{{if !@$screen}}grid print{{else}}main tbl{{/if}}">
  <thead>
  <tr>
    <th class="title">Code</th>
    <th class="title" style="width: auto;">{{mb_title class=CProduct field=name}}</th>
    <th class="title">Unités</th>
    <th class="title"></th>
    <th class="title">{{mb_title class=CProductOutput field=unit_price}}</th>
  </tr>
  </thead>
  
  {{foreach from=$return_form->_ref_outputs item=_output}}
    {{assign var=_stock value=$_output->_ref_stock}}
    {{assign var=_product value=$_stock->_ref_product}}
    <tr>
      <td style="text-align: right; white-space: nowrap;">
        {{mb_value object=$_product field=code}}
      </td>
      
      <td>
        <strong>{{mb_value object=$_product field=name}}</strong>
      </td>
      <td style="text-align: right; white-space: nowrap;">{{mb_value object=$_output field=quantity}}</td>
      <td style="white-space: nowrap;">{{$_product->item_title}}</td>
      <td style="white-space: nowrap; text-align: right;">{{mb_value object=$_output field=unit_price}}</td>
    </tr>
  {{/foreach}}
  
  <tr>
    <td colspan="11" style="padding: 0.5em; font-size: 1.1em;">
      <span style="float: right; text-align: right;">
        <strong>{{tr}}Total{{/tr}} : {{mb_value object=$return_form field=_total}}</strong><br />
      </span>
    </td>
  </tr>
</table>