{{*
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=genericImport script=generic_import register=true}}

<table class="main tbl">
  <tr>
    <th>{{tr}}OxImportPivot-Msg-Importable object{{/tr}}</th>
    <th class="narrow">
      <button type="button" class="download" onclick="GenericImport.downloadFile();">
        {{tr}}OxImportPivot-Action-Download file|pl{{/tr}}
      </button>
    </th>
  </tr>

  {{foreach from=$importable_objects item=_importable_object}}
    <tr>
      <td>{{tr}}{{$_importable_object}}{{/tr}}</td>
      <td class="narrow">
        <button type="button" class="search" onclick="GenericImport.showDetails('{{$_importable_object|replace:"\\":"\\\\"}}');">
          {{tr}}OxImportPivot-Action-Show details{{/tr}}
        </button>
        <button type="button" class="download" onclick="GenericImport.downloadFile('{{$_importable_object|replace:"\\":"\\\\"}}');">
          {{tr}}OxImportPivot-Action-Download file{{/tr}}
        </button>
      </td>
    </tr>
  {{/foreach}}
</table>
