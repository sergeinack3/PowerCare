{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=class      value=''}}
{{mb_default var=lite       value=false}}
{{mb_default var=paste      value=true}}
{{mb_default var=extensions value="dPfiles General extensions"|gconf}}
{{mb_default var=multi      value=true}}
{{mb_default var=notabindex value=false}}

{{unique_id var=uid_upload}}
{{assign var=_extensions value='/\s+/'|preg_split:$extensions}}
{{assign var=upload_max_filesize value="dPfiles General upload_max_filesize"|gconf}}
{{assign var=_max_size value='Ox\Core\CMbString::fromDecaBinary'|static_call:$upload_max_filesize}}

<script>
  Main.add(function(){
    App.loadJS(['modules/dPfiles/javascript/fileupload'], function(FileUpload) {
      new FileUpload($("{{$uid_upload}}").down("input"), {
        maxSize: '{{$_max_size}}'
      });
    });
  });
</script>

<div class="inline-upload">
  <div class="inline-upload-header">
    <label class="inline-upload-input {{if !$paste}}inline-upload-single{{/if}}" id="{{$uid_upload}}">
      <div class="inline-upload-input-text">
        <i class="far fa-folder-open"></i>

        {{if $lite}}
          {{tr}}common-action-Browse{{/tr}}
        {{else}}
          {{tr}}common-action-Browse or drag and drop file here{{/tr}}
        {{/if}}
      </div>

      <input type="file" name="formfile[]" class="{{$class}}" {{if $notabindex}}tabindex="-1"{{/if}} {{if $multi}}multiple{{/if}} accept="{{foreach from=$_extensions item=_ext}}.{{$_ext}},{{/foreach}}" />
    </label>

    {{if $paste}}
      <div class="inline-upload-pastearea" tabindex="{{if $notabindex}}-1{{else}}0{{/if}}">
        <i class="fa fa-clipboard"></i>

        {{if $lite}}
          {{tr}}common-action-Paste{{/tr}}
        {{else}}
          {{tr}}common-action-Paste image here{{/tr}}
        {{/if}}
      </div>
    {{/if}}
  </div>

  <progress class="inline-upload-progress" max="0" value="0" style="display: none;"></progress>

  <div class="inline-upload-files"></div>
</div>
