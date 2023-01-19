{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  var tables = {{$tables|@json}};

  function importNext() {
    var form = getForm("import-csv-tables");
    if (!getFirstTable(form)) {
      return;
    }
    form.submit();
  }

  function importError(table, message) {
    $("error-table-"+table).update(message);

    importNext();
  }

  function getFirstTable(form) {
    var table = tables.shift();
    if (!table) {
      return false;
    }

    $V(form.table, table);

    return true;
  }

  function importTable(table) {
    var form = getForm("import-csv-tables");

    var callback = $V(form.callback);
    $V(form.callback, '');
    $V(form.table, table);

    form.submit();

    $V(form.callback, callback);
  }
</script>

<h3>{{$dsn}}</h3>

<iframe name="import-csv-iframe" id="import-csv-iframe" style="display: none;"></iframe>

<form name="import-csv-tables" method="post" action="?" target="import-csv-iframe" onsubmit="return getFirstTable(this)">
  <input type="hidden" name="m" value="importTools" />
  <input type="hidden" name="dosql" value="do_import_csv_table" />
  <input type="hidden" name="dsn" value="{{$dsn}}" />
  <input type="hidden" name="table" value="" />
  <input type="hidden" name="callback" value="importNext" />
  <input type="hidden" name="callback_error" value="importError" />
  <table class="main form">
    <tr>
      <th>
        Répertoire
      </th>
      <td>
        <input type="text" name="csv_path" size="60" value="{{$csv_path}}" />
      </td>
      <th>
        Extension
      </th>
      <td>
        <input type="text" name="csv_extension" size="3" value="{{$csv_extension}}" />
      </td>
      <td>
        <button type="submit" class="submit">{{tr}}common-action-Import all{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<table class="tbl" style="width: auto;">
  <col style="width:200px" />
  <col style="width:10px"  />
  <col style="width:300px" />

  <tr>
    <th>Table</th>
    <th></th>
    <th></th>
  </tr>

  {{foreach from=$tables item=_table}}
    <tr>
      <td>{{$_table}}</td>
      <td>
        <button class="change notext compact" onclick="importTable('{{$_table}}')"></button>
      </td>
      <td>
        <progress id="csv-import-table-{{$_table}}" style="display: none; width: 150px;" class="process-progress"></progress>
        <span id="error-table-{{$_table}}"></span>
      </td>
    </tr>
  {{/foreach}}
</table>