{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=stock script=return_form}}
{{mb_script module=stock script=order_manager}}

<script type="text/javascript">
  Main.add(function () {
    window.onbeforeunload = window.onbeforeunload.wrap(function (old) {
      old();
      if (window.opener) {
        window.opener.refreshAll();
      }
    });

    filterReferences(getForm("filter-references"));
  });

  function changePage(start) {
    $V(getForm("filter-references").start, start);
  }

  function changeLetter(letter) {
    var form = getForm("filter-references");
    $V(form.start, 0, false);
    $V(form.letter, letter);
  }

  function filterReferences(form) {
    var url = new Url("dPstock", "httpreq_vw_references_list");
    url.addFormData(form);
    url.requestUpdate("list-references");
    return false;
  }

  function reloadReturnForms(return_form_id) {
    window.return_form_id = return_form_id;

    var url = new Url("dPstock", "httpreq_vw_return_forms_tabs");
    url.requestUpdate("return-forms-list");
  }

  function showProductDetails(product_id) {
    if (!product_id) {
      return;
    }

    var url = new Url("dPstock", "httpreq_vw_product_consumption_graph");
    url.addParam("product_id", product_id);
    url.addParam("width", 400);
    url.addParam("height", 150);
    url.requestUpdate("conso");
  }

  function returnCallback(product_output_id, product_output) {
    reloadReturnForms(product_output.return_form_id);
  }
</script>

<table class="main">
  <tr>
    <td class="halfPane" rowspan="2">
      <form action="?" name="filter-references" method="get" onsubmit="return filterReferences(this);">
        <input type="hidden" name="m" value="stock" />
        <input type="hidden" name="mode" value="return" />
        <input type="hidden" name="start" value="0" onchange="this.form.onsubmit()" />
        <input type="hidden" name="letter" value="{{$letter}}" onchange="this.form.onsubmit()" />

        <select name="category_id" style="max-width: 14em;" onchange="$V(this.form.start, 0, false); this.form.onsubmit();">
          <option value="">&mdash; {{tr}}CProductCategory.all{{/tr}} &mdash;</option>
          {{foreach from=$list_categories item=curr_category}}
          <option value="{{$curr_category->category_id}}">{{$curr_category->name}}</option>
          {{/foreach}}
        </select>

        <select name="societe_id" style="max-width: 14em;" onchange="$V(this.form.start, 0, false); this.form.onsubmit();">
          <option value="">&mdash; {{tr}}CSociete.all{{/tr}} &mdash;</option>
          {{foreach from=$list_societes item=_societe}}
          <option value="{{$_societe->_id}}">{{$_societe}}</option>
          {{/foreach}}
        </select>

        <input type="text" name="keywords" value="" size="10" onchange="$V(this.form.start, 0, false)" />

        <button type="button" class="search notext" name="search" onclick="this.form.onsubmit()">{{tr}}Search{{/tr}}</button>
        <button type="button" class="cancel notext" onclick="$(this.form).clear(false); this.form.onsubmit()"></button>

        {{mb_include module=system template=inc_pagination_alpha current=$letter change_page=changeLetter narrow=true}}
      </form>
      <div id="list-references"></div>
    </td>

    <td class="halfPane" id="return-forms-list">
      {{mb_include module=stock template=inc_return_forms_tabs}}
    </td>
  </tr>

  <tr>
    <td id="conso" style="height: 1%;">
      <div class="small-info">
        Cliquez sur une référence pour avoir un aperçu de la consommation du produit
      </div>
    </td>
  </tr>
</table>