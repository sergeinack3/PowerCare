{{*
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=update value=false}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="{{if $update}}4{{else}}3{{/if}}">
      {{tr}}ImportFileManager-Directory{{/tr}} : <em>{{$main_dir}}</em>
    </th>
  </tr>

  <tr>
    <th>{{tr}}ImportFileManager-List file name{{/tr}}</th>
    <th>{{tr}}ImportFileManager-List file size{{/tr}}</th>
    <th>{{tr}}ImportFileManager-List file date{{/tr}}</th>

    {{if $update}}
      <th>{{tr}}CImportFile-entity_type|pl{{/tr}}</th>
    {{/if}}
  </tr>

  {{foreach from=$files item=file}}
    {{assign var=file_name value=$file.import_file->file_name}}

    <tr>
      <td>{{$file_name}}</td>
      <td {{if !$file.file_size}}class="warning"{{/if}}>{{$file.file_size}}</td>
      <td {{if !$file.file_time}}class="warning"{{/if}} {{if $update && !$file.file_size}}colspan="2"{{/if}}>
        {{$file.file_time}}
      </td>

      {{if $update && $file.file_size}}
        <td>
          <form name="link-file-{{$file_name}}" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-link-file-{{$file_name}}')">
            <input type="hidden" name="m" value="genericImport"/>
            <input type="hidden" name="dosql" value="do_link_import_file"/>
            <input type="hidden" name="import_file_id" value="{{$file.import_file->_id}}"/>

            <select name="type">
              <option value="">-</option>
              {{foreach from=$entity_types item=_type}}
                <option value="{{$_type}}" {{if $file.import_file->entity_type === $_type}}selected{{/if}}>
                  {{tr}}mod-import-type-{{$_type}}{{/tr}}
                </option>
              {{/foreach}}
            </select>

            <button type="submit" class="change notext">{{tr}}Link{{/tr}}</button>
          </form>

          <div id="result-link-file-{{$file_name}}"></div>
        </td>
      {{/if}}
    </tr>

    {{foreachelse}}
    <tr>
      <td class="empty" colspan="{{if $update}}4{{else}}3{{/if}}">{{tr}}ImportFileManager-List file empty{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
