{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{tr}}mod-importTools-query-slave-only{{/tr}}
</div>

<h2>{{$info.name}} {{if $info.title}}&mdash; {{$info.title}} {{/if}}</h2>

<form name="search_line" method="get" onsubmit="return DatabaseExplorer.submitQuerySearch(this);">
  <input type="hidden" name="dsn" value="{{$dsn}}"/>
  <input type="hidden" name="table" value="{{$table}}"/>

  <table class="main form">
    <col style="width: 10%;" />

    {{foreach name=search from=$info.columns key=_key item=_col}}
      <tr>
        <th>{{$_key}}</th>

        <td class="narrow">
          <select name="select[{{$smarty.foreach.search.index}}]">
            <option value="like">LIKE</option>

            {{assign var=operands value='Ox\Import\ImportTools\CImportTools'|static:authorized_operands}}
            {{foreach from=$operands item=_operand}}
              <option value="{{$_operand}}">{{$_operand}}</option>
            {{/foreach}}
          </select>
        </td>

        <td><input name="where[{{$smarty.foreach.search.index}}]" type="text"/></td>
      </tr>
    {{/foreach}}
    <tr>
      <td class="button" colspan="3">
        <button type="submit" class="search">{{tr}}common-action-Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

