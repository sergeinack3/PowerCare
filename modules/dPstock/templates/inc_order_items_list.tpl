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
    {{if $order->object_id || $order->_has_lot_numbers}}
      {{if "dmi"|module_active}}
        <th class="title">LPP</th>
      {{/if}}
      <th class="title">Lot</th>
      <th class="title">Date pér.</th>
    {{/if}}
    <th class="title" style="width: auto;">{{mb_title class=CProduct field=name}}</th>
    <th class="title">Unités</th>
    <th class="title"></th>
    {{if $order->object_id || $order->comments|strpos:"Bon de retour" === 0}}
      <th class="title">{{mb_title class=CProductOrderItem field=renewal}}</th>
    {{/if}}
    <th class="title">{{mb_title class=CProductOrderItem field=unit_price}}</th>
    <th class="title">{{mb_title class=CProductOrderItem field=_price}}</th>
    <th class="title">{{mb_title class=CProductOrderItem field=tva}}</th>
  </tr>
  </thead>
  
  {{assign var=_class_comptable value=null}}
  
  {{foreach from=$order->_ref_order_items item=curr_item}}
    {{assign var=_reference value=$curr_item->_ref_reference}}
    {{assign var=_product value=$_reference->_ref_product}}
    
    {{if $_product->classe_comptable != $_class_comptable}}
      {{assign var=_class_comptable value=$_product->classe_comptable}}
      <tr>
        <th colspan="11" class="category" style="text-align: center;">{{$_class_comptable}}</th>
      </tr>
    {{/if}}
    <tr>
      <td style="text-align: right; white-space: nowrap;">
        {{if $curr_item->_ref_reference->supplier_code}}
          {{mb_value object=$curr_item->_ref_reference field=supplier_code}}
        {{else}}
          {{mb_value object=$curr_item->_ref_reference->_ref_product field=code}}
        {{/if}}
      </td>
      
      {{if $order->object_id || $order->_has_lot_numbers}}
        {{if $curr_item->_ref_lot}}
          {{if "dmi"|module_active}}
            <td>
              {{if isset($curr_item->_ref_dmi|smarty:nodefaults)}}
                {{$curr_item->_ref_dmi->code_lpp}}
              {{/if}}
            </td>
          {{/if}}
          <td>{{mb_value object=$curr_item->_ref_lot field=code}}</td>
          <td>{{mb_value object=$curr_item->_ref_lot field=lapsing_date}}</td>
        {{else}}
          {{if "dmi"|module_active}}
            <td></td>
          {{/if}}
          <td></td>
          <td></td>
        {{/if}}
      {{/if}}
      
      <td>
        <strong>{{mb_value object=$curr_item->_ref_reference->_ref_product field=name}}</strong>
        
        {{if $curr_item->septic}}
          (Déstérilisé)
        {{/if}}
      </td>
      <td style="text-align: right; white-space: nowrap;">{{mb_value object=$curr_item field=quantity}}</td>
      <td style="white-space: nowrap;">{{$curr_item->_ref_reference->_ref_product->item_title}}</td>
      
      {{if $order->object_id || $order->comments|strpos:"Bon de retour" === 0}}
        <td>{{mb_value object=$curr_item field=renewal}}</td>
      {{/if}}
      
      <td style="white-space: nowrap; text-align: right;">{{mb_value object=$curr_item field=unit_price}}</td>
      <td style="white-space: nowrap; text-align: right;">{{mb_value object=$curr_item field=_price}}</td>
      <td style="white-space: nowrap; text-align: right;">{{mb_value object=$curr_item field=tva decimals=1}}</td>
    </tr>
  {{/foreach}}
  
  <tr>
    <td colspan="11" style="padding: 0.5em; font-size: 1.1em;">
      <span style="float: right; text-align: right;">
        <strong>{{tr}}Total{{/tr}} : {{mb_value object=$order field=_total}}</strong><br />
        <strong>{{tr}}CProductOrder-_total_tva{{/tr}} : {{mb_value object=$order field=_total_tva}}</strong><br />
        {{mb_label object=$order->_ref_societe field=carriage_paid}} : {{mb_value object=$order->_ref_societe field=carriage_paid}}
      </span>
    </td>
  </tr>
</table>