{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $keydata}}
  <script>
    window.opener.RPU_Sender.updateKey("{{$fingerprint}}");
  </script>
{{/if}}

<h2>Import de la clé publique InVS</h2>

<form name="import" method="post" action="?m=urgences&{{$actionType}}={{$action}}&dialog=1" enctype="multipart/form-data">
  <input type="hidden" name="m" value="urgences" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />

  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />

  <button class="submit">{{tr}}Save{{/tr}}</button>
</form>

