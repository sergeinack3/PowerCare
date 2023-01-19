{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="check-tables-integrity" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-check-tables-integrity')">
  <input type="hidden" name="m" value="dPdeveloppement"/>
  <input type="hidden" name="a" value="ajax_check_tables_integrity"/>

  <table class="main form">
    <tr>
      <th>{{tr}}CTablesIntegrityChecker-dsn{{/tr}}</th>
      <td>
        <select name="dsn">
          <option value="">{{tr}}All{{/tr}}</option>
          {{foreach from=$dsn item=_dsn}}
            <option value="{{$_dsn}}">{{$_dsn}}</option>
          {{/foreach}}
        </select>
      </td>

      <th>{{tr}}CTablesIntegrityChecker-type{{/tr}}</th>
      <td>
        <label>
          <input type="radio" name="type" value="all" checked>
          {{tr}}CTablesIntegrityChecker.type.all{{/tr}}
        </label>
        <label>
          <input type="radio" name="type" value="table_missing">
          {{tr}}CTablesIntegrityChecker.type.table_missing{{/tr}}
        </label>
        <label>
          <input type="radio" name="type" value="class_missing">
          {{tr}}CTablesIntegrityChecker.type.class_missing{{/tr}}
        </label>
      </td>
    </tr>

    <tr>
      <th>{{tr}}CTablesIntegrityChecker-mod_name{{/tr}}</th>
      <td>
        <select name="mod_name">
          <option value="">{{tr}}All{{/tr}}</option>
          {{foreach from=$modules key=_mod_name item=_trad}}
            <option value="{{$_mod_name}}">{{$_trad}}</option>
          {{/foreach}}
        </select>
      </td>

      <td colspan="2"></td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="search">{{tr}}Check{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-check-tables-integrity"></div>
