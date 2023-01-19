{{*
 * @package Mediboard\dPhospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=order_col value="libelle"}}
{{mb_default var=order_way value="DESC"}}

<script>
  Main.add(function () {
    var tabs = Control.Tabs.create('tabs-configure', true);
  });
</script>

<form name="list_filter_stats_ufs" action="?" method="get" onsubmit="return onSubmitFormAjax(this, null, 'list_stats_ufs')">
  <input type="hidden" name="a" value="vw_stats_uf" />
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="onlyRefreshList" value="1" />
  <input type="hidden" name="uf_id" value="{{$uf->_id}}" />
  <input type="hidden" name="order_way" value="{{$order_way}}" />
  <input type="hidden" name="order_col" value="{{$order_col}}" />
</form>

<ul id="tabs-configure" class="control_tabs">
  {{foreach from=$type_affectations key=type item=tab_type}}
    <li><a href="#{{$type}}" {{if $tab_type|@count == 0}}class="empty"{{/if}}>{{tr}}{{$type}}{{/tr}} ({{$tab_type|@count}})</a></li>
  {{/foreach}}
</ul>

{{foreach from=$type_affectations key=type item=tab_type}}
  <div id="{{$type}}" style="display: none;">
    <table class="tbl">
      <tr>
        <th colspan="3">{{tr}}{{$type}}{{/tr}}</th>
      </tr>
      <th>
        {{mb_colonne class="CUniteFonctionnelle" field="libelle" order_col=$order_col order_way=$order_way function="UniteFonctionnelle.changeFilter"}}
      </th>
      {{if $type == "CProtocole"}}
        <th>
          {{mb_colonne class="CProtocole" field="chir_id" order_col=$order_col order_way=$order_way function="UniteFonctionnelle.changeFilter"}}
        </th>
        <th>
          {{mb_colonne class="CProtocole" field="function_id" order_col=$order_col order_way=$order_way function="UniteFonctionnelle.changeFilter"}}
        </th>
      {{/if}}
      {{foreach from=$tab_type item=_item}}
        <tr>
          {{if $type == "CProtocole"}}
            <td>{{$_item->_view}}</td>
            <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_item->_ref_chir}}</td>
            <td>{{mb_include module=mediusers template=inc_vw_function function=$_item->_ref_function}}</td>
          {{else}}
            <td>{{$_item->_ref_object->_view}}</td>
          {{/if}}

        </tr>
      {{/foreach}}
    </table>
  </div>
{{/foreach}}
