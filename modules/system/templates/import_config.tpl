{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  uploadCallback = function(obj) {
    $("systemMsg").insert(obj.message);
    window.opener.InteropActor.refreshConfigObjectValues(obj.object_id, obj.object_configs_guid);
  }
</script>

<form method="post" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog=1" name="formImportConfigXML" enctype="multipart/form-data" target="upload_iframe">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
  <input type="hidden" name="object_config_guid" value="{{$object_config_guid}}" />
  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />

  <input type="file" name="import" />
  <button class="submit">{{tr}}Save{{/tr}}</button>
</form>

<iframe id="upload_iframe" name="upload_iframe" src="about:blank" style="position: absolute; left: -10000px;"></iframe>