{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=tooltip value=false}}
{{assign var=change_page_args value="$dsn|$table|$column|$total"}}

<div id="distinct-values">
  <div class="small-info">{{$total}} valeurs.</div>

  {{if $dsn !== 'std' && $dsn !== 'slave' && $counts[0].row_count == 1}}
    <div class="small-info">Cette colonne peut être utilisée en tant que clé unique</div>
  {{/if}}

  <table class="main tbl">
    <tr>
      <td colspan="3" style="text-align: center;">
        <button type="button" class="lookup" onclick="DatabaseExplorer.seeColumnMetrics('{{$dsn}}', '{{$table}}', '{{$column}}');">
          {{tr}}importTools-action-See min, max and null values{{/tr}}
        </button>
      </td>
    </tr>

    <tr>
      <td colspan="3">
        {{mb_include module=system template=inc_pagination current=$start step=$step change_page="DatabaseExplorer.changePageDistinct"
        change_page_arg=$change_page_args show_results=false}}
      </td>
    </tr>

    <tr>
      <th>{{tr}}Rows{{/tr}}</th>
      <th>{{tr}}Value{{/tr}}</th>
      <th>{{tr}}Percentage{{/tr}}</th>
    </tr>

    {{foreach from=$counts item=_count}}
      <tr>
        <td class="text">
          {{mb_include module=importTools template=inc_display_value value=$_count.value col_info=$columns.$column}}
        </td>
        <td style="text-align: right;">{{$_count.row_count|integer}}</td>
        <td> {{$_count.percent|percent}} </td>
      </tr>
    {{/foreach}}
  </table>
</div>