{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=format_config_guid value=false}}

<script>
  uploadCallback = function() {
    const iframe = document.getElementById('upload_iframe')
    if (iframe.contentDocument && iframe.contentDocument.body.childElementCount > 0) {
      setTimeout(() => window.close(), 1000);
    }
  }
</script>

<form method="post" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog=1" name="formImportConfigXML" enctype="multipart/form-data" target="upload_iframe">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
  <input type="hidden" name="actor_guid" value="{{$actor_guid}}" />
  {{if $format_config_guid}}
    <input type="hidden" name="format_config_guid" value="{{$format_config_guid}}" />
  {{/if}}
  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />

  <input type="file" name="import" />
  <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
</form>
<iframe id="upload_iframe" name="upload_iframe" src="about:blank" style="position: absolute; top:100px; left:0px;" onload="uploadCallback()"></iframe>
