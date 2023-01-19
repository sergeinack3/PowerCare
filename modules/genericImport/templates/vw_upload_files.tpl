{{*
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=genericImport script=generic_import ajax=true}}

<form name="import-upload-files" method="post" enctype="multipart/form-data"
      onsubmit="return onSubmitFormAjax(this, null, 'result-import-upload-files');">
  <input type="hidden" name="m" value="genericImport"/>
  <input type="hidden" name="dosql" value="do_upload_files"/>

  <table class="main form">
    <tr>
      <th>{{tr}}CImportCampaign{{/tr}}</th>
      <td>
        <select id="import_campaign_id" name="import_campaign_id">
          {{foreach from=$campaigns item=campaign}}
            <option value="{{$campaign->_id}}" {{if $current_campaign_id === $campaign->_id}}selected{{/if}}>
              {{$campaign->name}}
            </option>
          {{/foreach}}
        </select>

        <button class="search notext" type="button" onclick="GenericImport.listFilesModal(this.form)"></button>
      </td>
    </tr>

    <tr>
      <th><label for="delete_files">{{tr}}ImportFilesManager-Action-Empty directory before import{{/tr}}</label></th>
      <td>
        <input type="checkbox" name="delete_files" value="1"/>
      </td>
    </tr>

    <tr>
      <th>{{tr}}common-directory-source{{/tr}}</th>
      <td>
        {{mb_include module=system template=inc_inline_upload paste=false extensions="zip csv" multi=true}}
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button class="fas fa-interal-link" type="submit">Importer</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-import-upload-files"></div>
