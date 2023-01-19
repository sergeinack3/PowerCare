{{*
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th colspan="4">{{tr}}{{$class_name}}{{/tr}}</th>
    <th class="narrow">
      <button type="button" class="download" onclick="GenericImport.downloadInfos('{{$class_name|replace:"\\":"\\\\"}}');">
        {{tr}}OxImportPivot-Action-Download file{{/tr}}
      </button>
    </th>
  </tr>

  {{if $add_infos}}
    <tr>
      <td colspan="5">
        <div class="small-info">
          {{'<br/>'|implode:$add_infos}}
        </div>

      </td>
    </tr>
  {{/if}}

  <tr>
    <th>{{tr}}FieldDescription-name{{/tr}}</th>
    <th>{{tr}}FieldDescription-size{{/tr}}</th>
    <th>{{tr}}FieldDescription-type{{/tr}}</th>
    <th>{{tr}}FieldDescription-description{{/tr}}</th>
    <th>{{tr}}FieldDescription-mandatory{{/tr}}</th>
  </tr>

  {{foreach from=$infos key=field_name item=description}}
    <tr>
      <td><strong>{{$field_name}}</strong></td>
      <td>{{$description->getSize()}}</td>
      <td>{{$description->getType()}}</td>
      <td class="text">{{$description->getDescription()}}</td>
      <td class="narrow">{{if $description->isMandatory()}}Oui{{else}}Non{{/if}}</td>
    </tr>
  {{/foreach}}
</table>
