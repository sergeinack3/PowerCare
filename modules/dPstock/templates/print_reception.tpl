{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Fermeture du tableau pour faire fonctionner le page-break -->
</td>
</tr>
</table>

{{assign var=nb_par_page value="25"}}

<style type="text/css">
  {{mb_include module=dPcompteRendu template='../css/print.css' header=4 footer=2 ignore_errors=true}}

  table.print td {
    font-size: 11px;
    font-family: Arial, Verdana, Geneva, Helvetica, sans-serif;
  }
</style>

<div class="header">
  <h1>
    <a href="#" onclick="window.print();">
      Bon de réception n°{{$reception->reference}} - {{$reception->_ref_group}}
    </a>
  </h1>
</div>

<div class="footer">
  <span style="float: right;">
    {{$dtnow|date_format:$conf.datetime}}
  </span>
  
  Bon de réception n°{{$reception->reference}}
</div>

<table class="form" style="margin-top: 5em;">
  <col style="width: 10%" />
  <col style="width: 40%" />
  <col style="width: 10%" />
  <col style="width: 40%" />

  <tr>
    <td colspan="2"></td>
    <th style="border: solid 1px black;"><strong>{{mb_label object=$reception field=_total_ttc}}</strong></th>
    <td style="border: solid 1px black;"><strong>{{mb_value object=$reception field=_total_ttc}}</strong></td>
  </tr>
  <tr>
    <td colspan="2"></td>
    <th>Fournisseur</th>
    <td>
      {{assign var=societe value=$reception->_ref_societe}}
      <strong>{{mb_value object=$societe field=name}}</strong><br />
      {{$societe->address|nl2br}}<br />
      {{mb_value object=$societe field=postal_code}} {{mb_value object=$societe field=city}}
      
      <br />
      {{if $societe->phone}}
        <br />
        {{mb_title object=$societe field=phone}}: {{mb_value object=$societe field=phone}}
      {{/if}}
      
      {{if $societe->fax}}
        <br />
        {{mb_title object=$societe field=fax}}: {{mb_value object=$societe field=fax}}
      {{/if}}
    </td>
  </tr>
</table>

<br />

<table class="grid print {{if $reception->_ref_reception_items|@count <= $nb_par_page}}bodyWithoutPageBreak{{else}}body{{/if}}">
  <col class="narrow" />
  <col class="narrow" />
  <col />
  <col class="narrow" />
  <col class="narrow" />
  
  <tr>
    <th class="category">{{mb_title class=CProductReference field=code}}</th>
    <th class="category">{{mb_title class=CProductOrderItemReception field=order_item_id}}</th>
    <th class="category narrow" style="width:28px;white-space: inherit;">{{mb_title class=CProductOrderItemReception field=quantity}}</th>
    <th class="category"></th>
    <th class="category narrow" style="width:28px;white-space: inherit;">{{mb_title class=CProductOrderItemReception field=code}}</th>
    <th class="category narrow" style="width:40px;white-space: inherit;">{{mb_title class=CProductOrderItemReception field=lapsing_date}}</th>
    <th class="category">{{mb_title class=CProductOrderItem field=_price}}</th>
    <th class="category">{{mb_title class=CProductOrderItemReception field=_price_tva}}</th>
    <th class="category">{{mb_title class=CProductOrderItemReception field=_price_ttc}}</th>
  </tr>

  {{assign var=classe_comptable_th value=null}}
  {{foreach from=$reception->_ref_reception_items item=curr_item name="foreach_products"}}
  {{assign var=nb_pages value=$smarty.foreach.foreach_products.total/$nb_par_page}}

  {{if !$smarty.foreach.foreach_products.first && $smarty.foreach.foreach_products.index%$nb_par_page == 0}}
</table>
{{assign var=iterations_restantes value=$smarty.foreach.foreach_products.total-$smarty.foreach.foreach_products.iteration}}
<table class="grid print {{if $iterations_restantes >= $nb_par_page}}body{{else}}bodyWithoutPageBreak{{/if}}">
  <tr>
    <th class="category">{{mb_title class=CProductReference field=code}}</th>
    <th class="category">{{mb_title class=CProductOrderItemReception field=order_item_id}}</th>
    <th class="category narrow" style="width:28px;white-space: inherit;">{{mb_title class=CProductOrderItemReception field=quantity}}</th>
    <th class="category"></th>
    <th class="category narrow" style="width:28px;white-space: inherit;">{{mb_title class=CProductOrderItemReception field=code}}</th>
    <th class="category narrow" style="width:40px;white-space: inherit;">{{mb_title class=CProductOrderItemReception field=lapsing_date}}</th>
    <th class="category">{{mb_title class=CProductOrderItem field=_price}}</th>
    <th class="category">{{mb_title class=CProductOrderItem field=_price}}</th>
    <th class="category">{{mb_title class=CProductOrderItemReception field=_price_tva}}</th>
    <th class="category">{{mb_title class=CProductOrderItemReception field=_price_ttc}}</th>
  </tr>
  {{/if}}

  {{assign var=classe_comptable value=$curr_item->_ref_order_item->_ref_reference->_ref_product->classe_comptable}}
  {{if !$classe_comptable}}
    {{assign var=classe_comptable value=0}}
  {{/if}}

  {{if $classe_comptable_th !== $classe_comptable}}
    {{assign var=classe_comptable_th value=$classe_comptable}}
    <tr>
      <th colspan="6" class="category" style="text-align: center;">{{if $classe_comptable_th}}{{$classe_comptable_th}}{{/if}}</th>
      <th class="category" style="text-align: left;">{{$classe_comptables.$classe_comptable.ht|string_format:"%.2f"|currency}}</th>
      <th class="category" style="text-align: left;">{{$classe_comptables.$classe_comptable.tva|string_format:"%.2f"|currency}}</th>
      <th class="category" style="text-align: left;">{{$classe_comptables.$classe_comptable.ttc|string_format:"%.2f"|currency}}</th>
    </tr>
  {{/if}}
  <tr>
    <td>{{mb_value object=$curr_item->_ref_order_item->_ref_reference field=code}}</td>
    <td>{{mb_value object=$curr_item field=order_item_id}}</td>
    <td style="text-align: right; white-space: nowrap;">{{mb_value object=$curr_item field=quantity}}</td>
    <td style="white-space: nowrap;">{{$curr_item->_ref_order_item->_ref_reference->_ref_product->item_title}}</td>
    <td style="text-align: center;" class="narrow">{{mb_value object=$curr_item field=code}}</td>
    <td class="narrow">{{mb_value object=$curr_item field=lapsing_date}}</td>
    <td class="narrow">{{mb_value object=$curr_item field=_price}}</td>
    <td class="narrow">{{mb_value object=$curr_item field=_price_tva}}</td>
    <td class="narrow">{{mb_value object=$curr_item field=_price_ttc}}</td>
  </tr>

  {{if $smarty.foreach.foreach_products.last}}
    <tr>
      <td colspan="6" style="font-size: 1.1em;">
        <span style="float: right;">
          <strong>{{tr}}Total{{/tr}}</strong>
        </span>
      </td>
      <td style="font-size: 1.1em;"><strong>{{mb_value object=$reception field=_total}}</strong></td>
      <td style="font-size: 1.1em;"><strong>{{mb_value object=$reception field=_total_tva}}</strong></td>
      <td style="font-size: 1.1em;"><strong>{{mb_value object=$reception field=_total_ttc}}</strong></td>
    </tr>
  {{/if}}
  {{/foreach}}
</table>

<!-- re-ouverture du tableau -->
<table>
  <tr>
    <td>