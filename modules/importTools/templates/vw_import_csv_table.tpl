{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  previewFile = function(form) {
    var url = new Url("importTools", "ajax_preview_csv_table");
    url.addElement(form.dsn);
    url.addElement(form.table);
    url.addElement(form.csv_path);
    url.addElement(form.csv_extension);

    return url.requestUpdate('import-csv-preview', {
      method: "post",
      getParameters: {
        m: "importTools",
        a: "ajax_preview_csv_table"
      }
    });
  }
</script>

<h3>{{$dsn}} - {{$table}}</h3>

<iframe name="import-csv-iframe" id="import-csv-iframe" style="display: none;"></iframe>

<form name="import-csv-table" method="post" action="?" target="import-csv-iframe">
  <input type="hidden" name="m" value="importTools" />
  <input type="hidden" name="dosql" value="do_import_csv_table" />
  <input type="hidden" name="dsn" value="{{$dsn}}" />
  <input type="hidden" name="table" value="{{$table}}" />
  <input type="hidden" name="preview" value="0" />
  <table class="main form">
    <tr>
      <th>
        Fichier CSV
      </th>
      <td>
        <input type="text" name="csv_path" size="60" value="{{$csv_path}}" />
      </td>
    </tr>
    <tr>
      <th>
        Extension
      </th>
      <td>
        <input type="text" name="csv_extension" size="3" value="{{$csv_extension}}" />
      </td>
    </tr>
    <tr>
      <td></td>
      <td>
        <button type="button" class="lookup" onclick="return previewFile(this.form)">{{tr}}Preview{{/tr}}</button>
        <button type="submit" class="submit">{{tr}}Import{{/tr}}</button>

        <progress id="csv-import-table" style="display: none; width: 200px;" class="process-progress"></progress>
      </td>
    </tr>
  </table>
</form>

<div id="import-csv-preview"></div>