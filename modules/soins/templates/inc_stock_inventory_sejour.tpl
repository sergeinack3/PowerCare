{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl main me-no-align">
  <tr>
    <th colspan="4" class="title">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
        {{$sejour->_view}}
      </span>
    </th>
  </tr>
  <tr>
    <th>{{tr}}CProduct{{/tr}}</th>
    <th class="narrow">{{tr}}CStockSejour{{/tr}}</th>
    <th>{{mb_label class=CStockSejour field=quantite_reelle}}</th>
    <th class="narrow">{{tr}}Action{{/tr}}</th>
  </tr>
  {{foreach from=$presriptions_by_cis item=_produit key=key_code_cis}}
    {{assign var=tab_code_cis value="-"|explode:$key_code_cis}}
    {{assign var=code_cis value=$tab_code_cis.1}}
    {{assign var=bdm value=$tab_code_cis.0}}
    {{assign var=count_stocks value=0}}
    {{if isset($stocks_sejour_by_cis.$code_cis|smarty:nodefaults)}}
      {{assign var=count_stocks value=$stocks_sejour_by_cis.$code_cis|@count}}
    {{/if}}
    <tr>
      <td rowspan="{{if $count_stocks}}{{$count_stocks}}{{/if}}">
        {{$_produit->_view}}
        <button class="list notext" type="button" title="{{tr}}CStockMouvement.all_for{{/tr}}: {{$_produit->_view}}" style="float:right;"
                onclick="Pharmacie.listMvtsStockPatient('{{$sejour->_id}}', '{{$key_code_cis}}', refreshInventorySejour.curry('{{$sejour->_id}}'));"></button>
      </td>
      {{if $count_stocks}}
        {{foreach from=$stocks_sejour_by_cis.$code_cis item=_stock key=code_cip name="stocks_cis"}}
          {{if !$smarty.foreach.stocks_cis.first}}</tr><tr>{{/if}}
          <td>{{$_stock->_libelle}}</td>
          <td style="text-align: center;">{{mb_value object=$_stock field=quantite_reelle}}</td>
          <td>
            <button class="add notext edit_stock_sejour" type="button" title="{{tr}}CStockMouvement-title-create{{/tr}}" style="float:right;"
                    onclick="Pharmacie.addStockPatient('{{$sejour->_id}}', '{{$key_code_cis}}', 'inventory', '{{$code_cip}}');"></button>
          </td>
        {{/foreach}}
      {{else}}
        <td class="empty">{{tr}}CStockSejour.none{{/tr}}</td>
        <td colspan="2">
          {{if $articles_by_cis.$code_cis}}
            <button class="add notext edit_stock_sejour" type="button" title="{{tr}}CStockMouvement-title-create{{/tr}}" style="float:right;"
                    onclick="Pharmacie.addStockPatient('{{$sejour->_id}}', '{{$key_code_cis}}', 'inventory');"></button>
          {{else}}
            <div  class="empty" style="float:right;">{{tr}}CProduitLivretTherapeutique.none_stock{{/tr}}</div>
            <br/>
            <span style="float:right;">
            {{foreach from=$produits_by_cis.$code_cis item=_produit_disp}}
              <button class="new" type="button"
                      onclick="Pharmacie.createStock('{{$_produit_disp->getId()}}', '{{$code_cis}}', '{{$bdm}}')">
                {{tr}}Create{{/tr}}
              </button>
              {{$_produit_disp->getLibelleCIP()}}
              <br />
            {{/foreach}}
            </span>
          {{/if}}
        </td>
      {{/if}}
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">{{tr}}CStockSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>