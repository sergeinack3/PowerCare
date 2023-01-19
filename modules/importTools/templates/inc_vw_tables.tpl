{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=display_all value=1}}

{{if $dsn === 'std' || $dsn === 'slave'}}
  {{assign var=display_all value=0}}
{{/if}}

{{if $display_all}}
  <h3><a href="#1" onclick="DatabaseExplorer.loadDbTables('{{$dsn}}');">{{$dsn}}</a></h3>

  <button onclick="DatabaseExplorer.importCSV('{{$dsn}}')" class="fa fa-download">CSV</button>

  <label style="float: right">
    <input type="checkbox" onclick="$('table-data').toggleClassName('fixed-width')" /> >-<
  </label>

  <label><input type="checkbox" value="show_hidden" onclick="DatabaseExplorer.toggleHidden(this.checked)" /> Cachés </label>
  <label><input type="checkbox" value="show_empty"  onclick="DatabaseExplorer.toggleEmpty(this.checked)" /> Vides </label>
  <label>
    <input type="checkbox" value="show_important" onclick="DatabaseExplorer.toggleImportant(this.checked);" />
    {{tr}}common-Important|pl{{/tr}}
  </label>

{{/if}}

<div style="overflow-y: scroll; height: 800px;">
  <table class="main tbl" id="table-{{$dsn}}">
    <tr>
      <th {{if $display_all}}colspan="2"{{/if}}>
        <label title="{{tr}}common-action-Search{{/tr}}">
          <i class="fa fa-search"></i>
          <input type="search" onkeyup="DatabaseExplorer.filterData(this, '.db-table');" onsearch="DatabaseExplorer.onFilterData(this, '.db-table');" />
        </label>
      </th>

      <th class="narrow">
        #
        {{if $order_col == 'count' && $order_way == 'ASC'}}
          <a style="display: inline-block" href="#1" onclick="DatabaseExplorer.loadDbTables('{{$dsn}}', 'count', 'DESC')"><i class="fas fa-sort-numeric-down"></i></a>
          {{else}}
          <a style="display: inline-block" href="#1" onclick="DatabaseExplorer.loadDbTables('{{$dsn}}', 'count', 'ASC')"><i class="fas fa-sort-numeric-up"></i></a>
        {{/if}}
      </th>

      <th class="narrow">
        <i class="fa fa-database"></i>
        {{if $order_col == 'size' && $order_way == 'ASC'}}
          <a style="display: inline-block" href="#1" onclick="DatabaseExplorer.loadDbTables('{{$dsn}}', 'size', 'DESC')"><i class="fas fa-sort-numeric-down"></i></a>
        {{else}}
          <a style="display: inline-block" href="#1" onclick="DatabaseExplorer.loadDbTables('{{$dsn}}', 'size', 'ASC')"><i class="fas fa-sort-numeric-up"></i></a>
        {{/if}}
      </th>

      {{if $display_all}}
        <th class="narrow"><i class="fa fa-exclamation-circle"></i></th>
      {{/if}}
    </tr>

    {{foreach from=$db_info.tables item=_table}}
      <tr {{if !$_table.display || $_table.count == 0}} style="display: none;" {{/if}} data-important="{{$_table.important}}"
        class="db-table {{if !$_table.display}} hidden {{/if}} {{if $_table.count == 0}} empty {{/if}}"
        id="col-{{$dsn}}-{{$_table.name}}">
        <td class="important_{{$_table.important}}">
          <a href="#1" onclick="return DatabaseExplorer.displayTableData('{{$dsn}}', '{{$_table.name}}')">
            {{$_table.name}}
          </a>
        </td>

        {{if $display_all}}
          <td class="important_{{$_table.important}}">
            {{if $_table.title}}
              <em>{{$_table.title|spancate:25}}</em>
            {{/if}}
          </td>
        {{/if}}

        <td class="compact important_{{$_table.important}}" style="text-align: right;">
          {{$_table.count|number_format:0:'.':' '}}
        </td>

        <td class="compact important_{{$_table.important}}" style="text-align: right;">
          {{$_table.size|decabinary}}
        </td>

        {{if $display_all}}
          <td class="compact important_{{$_table.important}}" style="text-align: center; cursor: pointer;">
            <i class="fa fa-exclamation-circle"
               title="{{tr}}common-action-Mark as important{{/tr}}"
               onclick="DatabaseExplorer.toggleImportantTable('{{$dsn}}', '{{$_table.name}}', this.up('tr').get('important'));"></i>
          </td>
        {{/if}}
      </tr>
    {{/foreach}}
  </table>
</div>