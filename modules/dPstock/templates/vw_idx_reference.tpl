{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=stock script=product_selector}}

<script>
  Main.add(function () {
    filterReferences(getForm("filter-references"));
    Control.Tabs.create("reference-tabs", true);
  });

  ProductSelector.init = function () {
    this.sForm = "edit_reference";
    this.sId = "product_id";
    this.sView = "product_name";
    this.sQuantity = "_unit_quantity";
    this.sUnit = "_unit_title";
    this.sPackaging = "packaging";
    this.pop({{$reference->product_id}});
  }

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
</script>

<table class="main">
  <tr>
    <td class="halfPane" rowspan="3">
      <form name="filter-references" action="?" method="get" onsubmit="return filterReferences(this)">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="start" value="0" onchange="this.form.onsubmit()" />
        <input type="hidden" name="letter" value="{{$letter}}" onchange="this.form.onsubmit()" />
        
        <select name="category_id" onchange="$V(this.form.start,0);this.form.onsubmit()">
          <option value="">&ndash; {{tr}}CProductCategory.all{{/tr}}</option>
          {{foreach from=$list_categories item=curr_category}}
            <option value="{{$curr_category->category_id}}" {{if $filter->category_id==$curr_category->_id}}selected="selected"{{/if}}>
              {{$curr_category->name}}
            </option>
          {{/foreach}}
        </select>
        
        {{mb_field object=$filter field=societe_id form="filter-references" autocomplete="true,1,50,false,true"
        style="width: 15em;" onchange="\$V(this.form.start,0)"}}
        
        <input type="text" name="keywords" value="{{$keywords}}" size="12" onchange="$V(this.form.start,0)" />
        
        <button type="submit" class="search notext">{{tr}}Filter{{/tr}}</button>
        <button type="button" class="cancel notext"
                onclick="$(this.form).clear(false); this.form.onsubmit();">{{tr}}Clear{{/tr}}</button>
        
        <br />
        <label>
          <input type="checkbox" name="show_all" {{if $show_all}}checked="checked"{{/if}}
                 onchange="$V(this.form.start,0); this.form.onsubmit();" />
          Afficher les archivés
        </label>
        
        {{mb_include module=system template=inc_pagination_alpha current=$letter change_page=changeLetter}}
      </form>

      <div id="list-references"></div>
    </td>


    <td class="halfPane">
      {{if $can->edit}}
        {{mb_include template=inc_form_reference}}
      {{/if}}
      
      {{if $reference->_id}}
        <ul class="control_tabs me-margin-left-4 me-margin-right-4" id="reference-tabs">
          <li>
            {{assign var=orders_count value=$lists_objects.orders|@count}}
            <a href="#reference-orders" {{if !$orders_count}}class="empty"{{/if}}>
              {{tr}}CProductOrder{{/tr}}
              <small>({{$orders_count}})</small>
            </a>
          </li>
          <li>
            {{assign var=receptions_count value=$lists_objects.receptions|@count}}
            <a href="#reference-receptions" {{if !$receptions_count}}class="empty"{{/if}}>
              {{tr}}CProductReception{{/tr}}
              <small>({{$receptions_count}})</small>
            </a>
          </li>
        </ul>
        <table id="reference-orders" style="display: block;" class="tbl">
          <tr>
            <th>{{mb_title class=CProductOrder field=order_number}}</th>
            <th>{{mb_title class=CProductOrder field=date_ordered}}</th>
            <th>{{mb_title class=CProductOrder field=_status}}</th>
          </tr>

          {{foreach from=$lists_objects.orders item=_order}}
            <tr>
              <td>
                <strong onmouseover="ObjectTooltip.createEx(this, '{{$_order->_guid}}')">
                  {{mb_value object=$_order field=order_number}}
                </strong>
              </td>
              <td>{{mb_value object=$_order field=date_ordered}}</td>
              <td>{{mb_value object=$_order field=_status}}</td>
            </tr>
            {{foreachelse}}
            <tr>
              <td colspan="10" class="empty">{{tr}}CProductOrder.none{{/tr}}</td>
            </tr>
          {{/foreach}}
        </table>
        <table class="tbl" id="reference-receptions" style="display: block;">
          <tr>
            <th></th>
            <th>Date de réception</th>
          </tr>
          {{foreach from=$lists_objects.receptions item=_reception}}
            <tr>
              <td>
                <strong onmouseover="ObjectTooltip.createEx(this, '{{$_reception->_guid}}')">
                  {{$_reception->reference}}
                </strong>
              </td>
              <td>{{mb_value object=$_reception field=date}}</td>
            </tr>
            {{foreachelse}}
            <tr>
              <td colspan="10" class="empty">{{tr}}CProductReception.none{{/tr}}</td>
            </tr>
          {{/foreach}}
        </table>
      {{/if}}

    </td>
  </tr>
</table>
