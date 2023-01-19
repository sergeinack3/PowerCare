{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=mediusers script=export_mediusers ajax=true}}

<div class="small-info">
  <h2 align="center">{{tr}}CMediusers-export-csv{{/tr}} CSV</h2>

  <strong>L'export est effectué pour l'établissement courant.</strong>
  <br/><br/>
  Les champs exportés sont les suivants :
  <ol>
    {{foreach from=$fields item=_field_name}}
      <li>
        <strong>{{$_field_name}}</strong> : {{tr}}CCSVImportMediusers-Msg-{{$_field_name}}-desc{{/tr}}

        {{if $_field_name == 'type'}}
          <button type="button" class="info notext" onclick="ExportMediusers.openTypeLibelle();">{{tr}}mod-mediusers-show-type-libelle{{/tr}}</button>
        {{/if}}
      </li>
    {{/foreach}}
  </ol>
  <br/>

  <div class="small-warning">
    {{tr}}CMediusersExportCsv-Msg-Options can slow down the server{{/tr}}
  </div>

  <ul>
    {{foreach from=$fields_opt item=_field_name}}
      <li>
        <label>
          <input type="checkbox" name="{{$_field_name}}" class="export_optionnal_field" value="1"/>
          <strong>{{$_field_name}}</strong> : {{tr}}CCSVImportMediusers-Msg-{{$_field_name}}-desc{{/tr}}
        </label>
      </li>
    {{/foreach}}
  </ul>
</div>

<table class="main form">
  <tr>
    <td class="button" colspan="2">
        <button type="button" class="fas fa-external-link-alt" onclick="ExportMediusers.exportMediusersCsv()">
          {{tr}}Export{{/tr}}
        </button>
    </td>
  </tr>
</table>
