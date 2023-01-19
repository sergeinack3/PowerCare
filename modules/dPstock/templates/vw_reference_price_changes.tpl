{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    getForm("form-filter-changes").ratio.addSpinner({min: 1});
  });

  fixOrderItem = function (form) {
    return onSubmitFormAjax(form, {
      onComplete: function () {
        form.up('tr').remove();
      }
    });
  }
</script>

<form name="form-filter-changes" method="get" action="?">
  <input type="hidden" name="m" value="dPstock" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />

  <table class="main form">
    <tr>
      <th>Rapport minimal entre les valeurs</th>
      <td class="narrow">
        <input type="text" name="ratio" value="{{$ratio}}" size="3" />
      </td>
      <td>
        <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<h3>
  {{$changes|@count}} prix unitaires suspects sur {{$changes_struct|@count}} références

  <small>({{$total_order_items}} lignes de commande au total)</small>
</h3>

<table class="main tbl">
  <tr>
    <th colspan="2">
      Reference
    </th>
    <td><strong>Prix unitaire <br />HT actuel</strong></td>
    <td></td>
    <td><strong>Réf.</strong></td>
    {{if "cahpp"|module_active}}
      <td><strong>CAHPP</strong></td>
    {{/if}}
    <td>Autre prix</td>
  </tr>
  <tr>
    <td style="text-align: right;">
      <small style="float: left; color: #666;">ID</small>
      Date de commande
    </td>
    <td>Numéro de commande</td>
    <td>Prix au moment<br /> de la commande</td>
    <td>Qté commandée</td>
    <td></td>
    {{if "cahpp"|module_active}}
      <td class="text">
        Cliquez sur le bouton
        <button class="tick notext"></button>
        de chaque ligne pour corriger le prix d'achat (peut être le prix actuel de la référence, celui enregistré dans la CAHPP ou un
        prix que vous choisissez).
      </td>
    {{/if}}
    <td></td>
  </tr>
  <tr>
    <td colspan="{{if "cahpp"|module_active}}7{{else}}6{{/if}}" style="background: none"><br /></td>
  </tr>

  {{foreach from=$changes_struct item=_changes key=reference_id}}
    <tr>
      <th colspan="2">
        <a class="button search notext" style="float: left;" href="?m=dPstock&tab=vw_idx_reference&reference_id={{$reference_id}}"></a>
        {{$references.$reference_id}}
        &mdash;
        {{$references.$reference_id->code}}
      </th>
      <td><strong>{{$references.$reference_id->price}}</strong></td>
      <td></td>
      <td>Réf.</td>
      {{if "cahpp"|module_active}}
        <td>CAHPP</td>
      {{/if}}
      <td>Autre prix</td>
    </tr>
    {{foreach from=$_changes item=_change}}
      <tr>
        <td style="text-align: right;">
          <small style="float: left; color: #666;">{{$_change.order_item_id}}</small>
          {{$_change.date_ordered|date_format:$conf.longdate}}
        </td>
        <td>
          <span
            onmouseover="ObjectTooltip.createEx(this, '{{$_change.order_item->_ref_order->_guid}}')">{{$_change.order_number}}</span>
        </td>
        <td {{* style="color: {{if $_change.OP > $_change.RP}} red {{else}} green {{/if}}" *}}>{{$_change.OP}}</td>
        <td>{{$_change.OQ}}</td>
        <td>
          <form name="fix-price-ref-{{$_change.order_item_id}}" method="post" onsubmit="return fixOrderItem(this)">
            <input type="hidden" name="m" value="dPstock" />
            <input type="hidden" name="dosql" value="do_order_item_aed" />
            <input type="hidden" name="order_item_id" value="{{$_change.order_item_id}}" />
            <input type="hidden" name="unit_price" value="{{$references.$reference_id->price}}" />
            <button class="tick notext"></button>
          </form>
        </td>
        {{if "cahpp"|module_active}}
          <td class="text {{if $references_cahpp.$reference_id->prix_unitaire == null}}empty{{/if}}">
            {{if $references_cahpp.$reference_id->prix_unitaire != null}}
              <form name="fix-price-cahpp-{{$_change.order_item_id}}" method="post" onsubmit="return fixOrderItem(this)">
                <input type="hidden" name="m" value="dPstock" />
                <input type="hidden" name="dosql" value="do_order_item_aed" />
                <input type="hidden" name="order_item_id" value="{{$_change.order_item_id}}" />
                <input type="hidden" name="unit_price" value="{{$references_cahpp.$reference_id->prix_unitaire}}" />
                <button class="tick notext"></button>
              </form>
              {{$references_cahpp.$reference_id->prix_unitaire}}
              &mdash;
              {{$references_cahpp.$reference_id|spancate:40}}</small>
            {{else}}
              Aucun prix CAHPP
            {{/if}}
          </td>
        {{/if}}

        <td>
          <form name="fix-price-custom-{{$_change.order_item_id}}" method="post" onsubmit="return fixOrderItem(this)">
            <input type="hidden" name="m" value="dPstock" />
            <input type="hidden" name="dosql" value="do_order_item_aed" />
            <input type="hidden" name="order_item_id" value="{{$_change.order_item_id}}" />
            <input type="text" name="unit_price" value="{{$_change.OP}}" size="4" />
            <button class="tick notext"></button>
          </form>
        </td>
      </tr>
    {{/foreach}}
    <tr>
      <td colspan="{{if "cahpp"|module_active}}7{{else}}6{{/if}}" style="background: none"><br /></td>
    </tr>
  {{/foreach}}
</table>