{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=storageEngine value=$tablesInformationArray.tableEngine}}
{{assign var=tableSize value=$tablesInformationArray.tableDataLength}}

{{assign var=spanStyle value=""}}
{{if $tableSize > 1073741824}}
  {{assign var=spanStyle value='style=font-weight:bold;'}}
{{/if}}
<td>
  <span {{$spanStyle}}>{{$tablesInformationArray.tableDataLength|decabinary}}</span>
</td>
<td>
  {{if $storageEngine|mb_strtolower !== "myisam"}}
    <span class="error">{{$storageEngine}}</span>
  {{else}}
    {{$storageEngine}}
  {{/if}}
</td>