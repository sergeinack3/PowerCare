{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=search value=false}}

<table class="tbl db-data" style="width: auto;">
  <tr>
    <th class="narrow">
      <button type="button" class="lookup notext compact" onclick="DatabaseExplorer.viewSearchLines('{{$dsn}}' ,'{{$table}}')">
        {{tr}}common-action-Search{{/tr}}
      </button>

      {{if $display_all}}
        <br/>
        <button type="button" class="fa fa-database notext compact" onclick="DatabaseExplorer.seeTableSchema('{{$dsn}}' ,'{{$table}}');">
          SQL
        </button>
      {{/if}}

      {{if $search}}
        <br/>
        <button class="download" onclick="DatabaseExplorer.exportResult()">CSV</button>
      {{/if}}

    </th>

    {{foreach from=$columns key=_col item=_col_info}}
      <th title="{{$_col_info.datatype}}" style="padding: 2px 4px;" {{if $_col_info.hide}}class="hidden"{{/if}}>
        {{assign var=_new_order_way value="ASC"}}
        {{if $order_way == "ASC"}}
          {{assign var=_new_order_way value="DESC"}}
        {{/if}}

        {{if $search}}
          {{$_col}}
        {{else}}
          <a class="{{$_col}} {{if $order_column == $_col}}sorted {{$order_way}}{{else}}sortable{{/if}}"
             onclick="DatabaseExplorer.displayTableData('{{$dsn}}', '{{$table}}', 0, null, '{{$_col}}', '{{$_new_order_way}}')">
            {{$_col}}
          </a>
        {{/if}}


        <div>
          {{if $_col_info.Key == "PRI"}}
            <i class="fa fa-key" style="color: red;{{if $display_all}} cursor: pointer;{{/if}}"
            {{if $display_all}}onclick="DatabaseExplorer.selectPrimaryKey('{{$dsn}}', '{{$table}}', '{{$_col}}')"{{/if}}></i>
          {{elseif $_col_info.Key == "MUL"}}
            <i class="fa fa-link"  style="color: goldenrod;{{if $display_all}} cursor: pointer;{{/if}}"
               {{if $display_all}}onclick="DatabaseExplorer.selectPrimaryKey('{{$dsn}}', '{{$table}}', '{{$_col}}')"{{/if}}></i>
          {{/if}}

          <button class="lookup notext"
                  onclick="DatabaseExplorer.displayTableDistinctData('{{$dsn}}', '{{$table}}', '{{$_col}}')"></button>

          {{if $display_all}}
            {{if $_col_info.hide}}
              <i class="fa fa-expand" style="cursor: pointer; color: limegreen;" title="{{tr}}common-action-Display{{/tr}}"
                 onclick="DatabaseExplorer.saveColumnInfo('{{$dsn}}', '{{$table}}', '{{$_col}}', 'hide', '0');"></i>
            {{else}}
              <i class="fa fa-compress" style="cursor: pointer;" title="{{tr}}common-action-Hide{{/tr}}"
                 onclick="DatabaseExplorer.saveColumnInfo('{{$dsn}}', '{{$table}}', '{{$_col}}', 'hide', '1');"></i>
            {{/if}}
          {{/if}}
        </div>

        <input type="search" size="5" onkeyup="DatabaseExplorer.searchColumn(this, 'col-{{$_col}}')"/>
      </th>
    {{/foreach}}
  </tr>

  {{foreach from=$rows item=_row}}
    <tr class="search-keyword">
      <td>
        {{if $display_all && $table_info.primary_key}}
          {{assign var=pk value=$table_info.primary_key}}
          <button class="search notext compact"
                  onclick="DatabaseExplorer.showLine('{{$dsn}}', '{{$table}}','{{$pk}}' , '{{$_row.$pk}}', '{{$count}}')"></button>
        {{/if}}

      </td>
      {{foreach from=$columns key=_col item=_col_info}}
        <td {{if $_col_info.Key == "MUL"}} style="background: rgba(127,180,127,0.2);" {{/if}}
                class="col-{{$_col}} {{if $_col_info.hide}} hidden{{/if}}">
          {{mb_include module=importTools template=inc_display_value value=$_row.$_col|smarty:nodefaults col_info=$_col_info}}
        </td>
      {{/foreach}}
    </tr>
  {{/foreach}}
</table>