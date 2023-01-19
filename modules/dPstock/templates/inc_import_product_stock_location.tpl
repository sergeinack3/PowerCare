{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(function () {

    Control.Tabs.create('psl_import_tabs');
  });
</script>
<div id="error_container">
  {{if $errors|@count !== 0}}
    {{mb_include template=import_product_stock_error}}
  {{/if}}

  {{if $pending_lines.total > 0}}
    <div class="small-info">
      <span>{{tr var1=$processed_lines}}CProductStockLocation.processed_imports{{/tr}}</span>
      <span>{{tr var1=$pending_lines.total}}CProductStockLocation.pending_imports{{/tr}}</span>
    </div>
  {{/if}}
</div>
<table class="main me-no-align">
  <tr>
    <td>
      <form name="affectationProductStockLocation" action="?" method="post"
            onsubmit="return ProductStock.validImport();">
        <ul id="psl_import_tabs" class="control_tabs">
          {{if $pending_lines.CService|@count}}
            <li><a href="#import_services">{{tr}}CService{{/tr}}</a></li>
          {{/if}}
          {{if $pending_lines.CBlocOperatoire|@count}}
            <li><a href="#import_blocs">{{tr}}CBlocOperatoire{{/tr}}</a></li>
          {{/if}}
        </ul>
        <input type="hidden" name="m" value="stock"/>
        <input type="hidden" name="dosql" value="do_import_product_stock_location"/>
        <div style="text-align: center;">
          {{if $pending_lines.total > 0}}
            <div id="import_services" class="me-padding-0" style="display: none;height:450px;overflow: auto;">
              {{if $pending_lines.CService|@count}}
                <table class="tbl me-no-align">
                  <tr>
                    <th class="title">{{mb_label class=CProductStockLocation field=name}}</th>
                    <th class="title">{{mb_label class=CProductStockLocation field=desc}}</th>
                    <th class="title narrow">{{tr}}CProductStockLocation-position-court{{/tr}}</th>
                    <th class="title narrow">{{mb_label class=CProductStockLocation field=object_id}}</th>
                    <th class="title narrow"></th>
                  </tr>
                  {{foreach from=$pending_lines.CService item=_line name=p_service}}
                    <tr id="psl_s_{{$smarty.foreach.p_service.index}}">
                      <td>
                        {{$_line.name}}
                      </td>
                      <td {{if $_line.desc === "" }}class="empty"{{/if}}>
                        {{if $_line.desc !== "" }}
                          {{$_line.desc}}
                        {{else}}
                          {{tr}}CProductStockLocation-import-no-desc{{/tr}}
                        {{/if}}
                      </td>
                      <td>
                        {{$_line.position}}
                      </td>
                      <td class="me-text-align-center">
                        <input type="hidden" name="psl_s[{{$smarty.foreach.p_service.index}}][name]"
                               value="{{$_line.name}}">
                        <input type="hidden" name="psl_s[{{$smarty.foreach.p_service.index}}][desc]"
                               value="{{$_line.desc}}">
                        <input type="hidden" name="psl_s[{{$smarty.foreach.p_service.index}}][position]" value="{{$_line.position}}">
                        <input type="hidden" name="psl_s[{{$smarty.foreach.p_service.index}}][actif]" value="{{$_line.actif}}">
                        <select name="psl_s[{{$smarty.foreach.p_service.index}}][object_id]" class="psl_s_select"
                                id="psl_s_object_id_{{$smarty.foreach.p_service.index}}">
                          <option value="" selected>{{tr}}CProductStockLocation.select-service{{/tr}}</option>
                          {{foreach from=$location_services item=_emplacement}}
                            <option value="{{$_emplacement->_id}}">{{$_emplacement->_view}}</option>
                            {{foreachelse}}
                            <option value="" disabled>{{tr}}CProductStockLocation.no-service{{/tr}}</option>
                          {{/foreach}}
                        </select>
                      </td>
                      <td>
                        {{if $smarty.foreach.p_service.first && $pending_lines.CService|@count !== 1}}
                          <button type="button" onclick="ProductStock.applyToAll('psl_s')" class="down arrow-down notext"
                                  title="{{tr}}CProductStockLocation.apply-to-all{{/tr}}"></button>
                        {{/if}}
                      </td>
                    </tr>
                  {{/foreach}}
                </table>
              {{/if}}
            </div>
            <div id="import_blocs" class="me-padding-0" style="display: none;height:450px;overflow: auto;">
              {{if $pending_lines.CBlocOperatoire|@count}}
                <table class="tbl me-no-align">
                  <tr>
                    <th class="title">{{mb_label class=CProductStockLocation field=name}}</th>
                    <th class="title">{{mb_label class=CProductStockLocation field=desc}}</th>
                    <th class="title narrow">{{tr}}CProductStockLocation-position-court{{/tr}}</th>
                    <th class="title narrow">{{mb_label class=CProductStockLocation field=object_id}}</th>
                    <th class="title narrow"></th>
                  </tr>
                  {{foreach from=$pending_lines.CBlocOperatoire item=_line name=p_bloc}}
                    <tr id="psl_b_{{$smarty.foreach.p_bloc.index}}">
                      <td>
                        {{$_line.name}}
                      </td>
                      <td {{if $_line.desc === "" }}class="empty"{{/if}}>
                        {{if $_line.desc !== "" }}
                          {{$_line.desc}}
                        {{else}}
                          -
                        {{/if}}
                      </td>
                      <td>
                        {{$_line.position}}
                      </td>
                      <td class="me-text-align-center">
                        <input type="hidden" name="psl_b[{{$smarty.foreach.p_bloc.index}}][name]" value="{{$_line.name}}">
                        <input type="hidden" name="psl_b[{{$smarty.foreach.p_bloc.index}}][desc]" value="{{$_line.desc}}">
                        <input type="hidden" name="psl_b[{{$smarty.foreach.p_bloc.index}}][position]" value="{{$_line.position}}">
                        <input type="hidden" name="psl_b[{{$smarty.foreach.p_bloc.index}}][actif]" value="{{$_line.actif}}">
                        <select name="psl_b[{{$smarty.foreach.p_bloc.index}}][object_id]"
                                id="psl_b_object_id_{{$smarty.foreach.p_bloc.index}}" class="psl_b_select">
                          <option value="" selected>{{tr}}CProductStockLocation.select-bloc{{/tr}}</option>
                          {{foreach from=$location_blocs item=_emplacement}}
                            <option value="{{$_emplacement->_id}}">{{$_emplacement->_view}}</option>
                            {{foreachelse}}
                            <option value="" disabled>{{tr}}CProductStockLocation.no-bloc{{/tr}}</option>
                          {{/foreach}}
                        </select>
                      </td>
                      <td>
                        {{if $smarty.foreach.p_bloc.first && $pending_lines.CBlocOperatoire|@count !== 1}}
                          <button type="button" onclick="ProductStock.applyToAll('psl_b')" class="down arrow-down notext"
                                  title="{{tr}}CProductStockLocation.apply-to-all{{/tr}}"></button>
                        {{/if}}
                      </td>
                    </tr>
                  {{/foreach}}
                </table>
              {{/if}}
            </div>
          {{/if}}
          <button class="button cancel me-margin-top-4"
                  onclick="Control.Modal.close();this.refreshTab('vw_idx_stock_location');">{{tr}}Cancel{{/tr}}</button>
          <button type="submit" class="submit me-margin-top-4">{{tr}}Save{{/tr}}</button>
        </div>
      </form>
    </td>
  </tr>
</table>
