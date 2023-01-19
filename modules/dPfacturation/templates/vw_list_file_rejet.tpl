{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$chir_id}}{{mb_return}}{{/if}}

{{if !$fs_source_reception->_id || !$fs_source_reception->active}}
  <div class="small-warning">
    <strong>{{tr}}CRejet-fs_source_reception-none{{/tr}}</strong>
  </div>
  {{mb_return}}
{{/if}}
{{if $erreur}}
  <div class="small-error">{{$erreur}}</div>
  {{mb_return}}
{{/if}}

<table class="tbl">
  {{if $count_files >= 1000}}
    <tr>
      <td>
        <div class="small-warning">
          <strong>
            {{tr var1=$fs_source_reception->host var2=$count_files}}CRejet-fs_source_reception-too_much{{/tr}}
          </strong>
        </div>
      </td>
    </tr>
  {{else}}
    <tr>
      <th colspan="2">
        {{tr}}utilities-source-file_system-getFiles{{/tr}}: ({{$count_files}})
        {{if $count_files}}
          <button type="button" class="copy" onclick="Rejet.traitementXML('{{$chir_id}}');" style="float: right;"
            {{if $count_files >= 100}}
              disabled="disabled" title="{{tr}}CRejet-fs_source_reception-too_much_for_treat{{/tr}}"
            {{/if}}>
            {{tr}}Treat{{/tr}}
          </button>
        {{/if}}
      </th>
    </tr>
    {{foreach from=$files item=_file}}
      <tr>
        <td class="text">{{$_file}} </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td class="empty">{{tr}}CRejet-fs_source_reception-no_file{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>
