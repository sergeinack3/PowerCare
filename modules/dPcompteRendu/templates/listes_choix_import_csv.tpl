{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{tr var1=$owner}}CListeChoix-import_for{{/tr}}</h2>

<form method="post" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog=1&owner_guid={{$owner_guid}}" name="import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />

  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />

  <button class="submit">{{tr}}Save{{/tr}}</button>
</form>

{{$app->getMsg()|smarty:nodefaults}}