{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPdeveloppement script=table_integrity ajax=true}}

<table class="main tbl">
  <thead>
  <tr>
    <th>
      <a class="sortable" onclick="TableIntegrity.sortIntegrityByColumn(this, 0);">
        {{tr}}CTableIntegrity-dsn{{/tr}}
      </a>
    </th>
    <th>
      <a class="sortable" onclick="TableIntegrity.sortIntegrityByColumn(this, 1);">
        {{tr}}CTableIntegrity-class_name{{/tr}}
      </a>
    </th>
    <th>
      <a class="sortable" onclick="TableIntegrity.sortIntegrityByColumn(this, 2);">
        {{tr}}CTableIntegrity-table_name{{/tr}}
      </a>
    </th>
    <th>
      <a class="sortable" onclick="TableIntegrity.sortIntegrityByColumn(this, 3, 1);">
        {{tr}}CTableIntegrity-row_count{{/tr}}
      </a>
    </th>
  </tr>
  </thead>

  <tbody>
  {{foreach from=$integrity_result item=_result}}
    <tr>
      <td>{{$_result->getDsn()}}</td>
      <td>{{$_result->getClassName()}}</td>
      <td {{if !$_result->getTableExists()}}class="warning"{{/if}}>
        {{$_result->getTableName()}}
      </td>
      <td>{{$_result->getRowCount()}}</td>
    </tr>
  {{/foreach}}
  </tbody>
</table>

