{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>
  {{$table}}
  &ndash;
  {{if $where_column}}
    WHERE {{$where_column}} = '{{$where_value}}'
  {{/if}}
</h2>

{{if $rows|@count > 0}}
  <table class="main layout">
    <tr>
      <td>
        <table class="tbl" style="width: auto;">
          {{foreach from=$columns key=_col item=_col_info}}
            <tr>
              <th style="text-align: left;">{{$_col}}</th>
              <td>
                {{if $_col_info.Key == "PRI"}}
                  <i class="fa fa-key" onclick="DatabaseExplorer.selectPrimaryKey('{{$dsn}}', '{{$table}}', '{{$_col}}')"
                     style="color: red; cursor: pointer;"></i>
                {{elseif $_col_info.Key == "MUL"}}
                  <i class="fa fa-link" onclick="DatabaseExplorer.selectPrimaryKey('{{$dsn}}', '{{$table}}', '{{$_col}}')"
                     style="color: goldenrod; cursor: pointer;"></i>
                {{/if}}
              </td>
              <td style="color: #999;"><code>{{$_col_info.datatype}}</code></td>
              <td>
                {{mb_include module=importTools template=inc_display_value value=$rows.0.$_col col_info=$_col_info}}
              </td>
            </tr>
          {{/foreach}}
        </table>
      </td>
      {{if $line_compare}}
        <td style="vertical-align: top">
          {{mb_include module=importTools template=inc_vw_mb_imported_object object=$mb_object}}
        </td>
      {{/if}}
    </tr>
  </table>
{{else}}
  <span class="empty">{{tr}}No result{{/tr}}</span>
{{/if}}