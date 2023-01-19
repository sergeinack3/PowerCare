{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=form}}

<script>
  updateColumnSchema = function (form, element) {
    var col_name = element.get('col');
    var col_type = $V(element.down('select'));

    $V(form.elements.column_name, col_name);
    $V(form.elements.column_type, col_type);

    form.onsubmit();
  }
</script>

<h2>{{$dsn}} &mdash; {{$table_info.name}}</h2>


<form name="{{$form}}" method="post" onsubmit="return onSubmitFormAjax(this, function() { DatabaseExplorer.urlTableSchema.refreshModal(); });">
  <input type="hidden" name="m" value="importTools" />
  <input type="hidden" name="dosql" value="do_update_table_schema" />
  <input type="hidden" name="dsn" value="{{$dsn}}" />
  <input type="hidden" name="table" value="{{$table}}" />

  <input type="hidden" name="column_name" value="" />
  <input type="hidden" name="column_type" value="" />
</form>

<table class="main tbl">
  <tr>
    <th class="narrow">{{tr}}common-Column{{/tr}}</th>
    <th class="narrow"></th>
    <th class="narrow">{{tr}}common-Type{{/tr}}</th>
    <th class="narrow"></th>
    <th></th>
  </tr>

  {{foreach from=$table_info.columns key=_col item=_col_info}}
    <tr data-col="{{$_col}}">
      <td>{{$_col}}</td>

      <td>
        {{if $_col_info.Key == "PRI"}}
          <i class="fa fa-key" onclick="DatabaseExplorer.selectPrimaryKey('{{$dsn}}', '{{$table}}', '{{$_col}}')"
             style="color: red; cursor: pointer;"></i>
        {{elseif $_col_info.Key == "MUL"}}
          <i class="fa fa-link" onclick="DatabaseExplorer.selectPrimaryKey('{{$dsn}}', '{{$table}}', '{{$_col}}')"
             style="color: goldenrod; cursor: pointer;"></i>
        {{/if}}
      </td>

      <td style="color: #999;">
        <code>{{$_col_info.datatype}}</code>
      </td>

      <td>
        <select>
          {{foreach from='Ox\Import\ImportTools\CImportTools'|static:authorized_datatypes key=_label item=_types}}
            <optgroup label="{{tr}}importTools.datatype.{{$_label}}{{/tr}}">
              {{foreach from=$_types item=_type}}
                <option value="{{$_type}}">{{$_type}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>

      <td>
        <button type="button" class="save notext compact" onclick="updateColumnSchema(getForm('{{$form}}'), this.up('tr'));">
          {{tr}}common-action-Save{{/tr}}
        </button>
      </td>
    </tr>
  {{/foreach}}
</table>