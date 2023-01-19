{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=stock script=mouvement ajax=true}}
<table class="tbl" id="list_mvts_stock">
  <tr>
    <th>{{mb_title class=CStockMouvement field=source_id}}</th>
    <th>{{mb_colonne class=CStockMouvement field=cible_id order_col=$order_col order_way=$order_way
        function="refreshMvts"}}</th>
    <th>{{mb_title class=CProductStockGroup field=product_id}}</th>
    <th>{{mb_title class=CStockMouvement field=type}}</th>
    <th>{{mb_title class=CStockMouvement field=quantite}}</th>
    <th class="narrow">{{mb_title class=CStockMouvement field=datetime}}</th>
    <th>{{mb_title class=CStockMouvement field=commentaire}} <br> {{tr}}CStockMouvement{{/tr}}</th>
    <th>{{tr}}Comment{{/tr}} <br> {{tr}}CPriseDispensation-delivery_id-court{{/tr}}</th>
    <th>{{mb_title class=CStockMouvement field=etat}}</th>
    <th class="narrow button">
      <form name="validate_checked_mvts" method="post" action="?" onsubmit="return onSubmitFormAjax(this, refreshMvts);">
        <input type="hidden" name="m" value="stock" />
        <input type="hidden" name="dosql" value="do_realise_mouvements" />
        <input type="hidden" name="mvts_ids" value="" />
        <button type="button" class="tick oneclick singleclick"
                onclick="Mouvement.send(this.form);">{{tr}}CStockMouvement-action-validate_list{{/tr}}</button>
      </form>
      <br />
      <input name="check_all_mvts" type="checkbox" onchange="Mouvement.selectAll($V(this));" id="check_all_mvts" />
    </th>
  </tr>

  {{foreach from=$mvts_stock item=_mvt_stock}}
    <tr id="{{$_mvt_stock->_guid}}">
      {{assign var=id value=$_mvt_stock->_id}}
      {{if array_key_exists($id, $commentaire_dispensation)}}
        {{mb_include module=stock template=vw_mouvement_stock commentaire_dispensation=$commentaire_dispensation.$id}}
      {{else}}
        {{mb_include module=stock template=vw_mouvement_stock commentaire_dispensation=""}}
      {{/if}}
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="10">{{tr}}CStockMouvement.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
