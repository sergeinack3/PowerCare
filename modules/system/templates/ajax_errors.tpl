{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script_register_end}}

<script type="text/javascript">
  {{if !$app->user_id}}
  User = {};
  AjaxResponse.onDisconnected();
  if (Object.isFunction(AjaxResponse.onCompleteDisconnected)) {
    var onCompleteDisconnected = AjaxResponse.onCompleteDisconnected;
    AjaxResponse.onCompleteDisconnected = null;
    onCompleteDisconnected();
  }
  {{else}}
  delete Url.pendingRequests['{{$requestID}}'];
  {{assign var=user value=$app->_ref_user}}

  {{if isset($app->_ref_user|smarty:nodefaults)}}
  if (User.id && User.id != '{{$app->_ref_user->_id}}') {
    alert($T('common-error-Ajax request user context error: %s instead of %s.', (User.view || '').trim(), '{{$app->_ref_user->_view|trim}}'));
    document.location.reload();
  }
  {{/if}}

  {{if $user}}
  User = {{$user->_basic_info|@json}};
  {{else}}
  User = {};
  {{/if}}

  if (Object.isFunction(AjaxResponse.onComplete)) {
    var onComplete = AjaxResponse.onComplete;
    AjaxResponse.onComplete = null;
    onComplete();
  }


  // Div performance
  {{if $show_performance}}
    AjaxResponse.onLoaded({{$smarty.get|@json}}, {{$performance|@json}});
  {{/if}}

  {{/if}}
</script>

{{if !$app->user_id}}
  <div class="error">{{tr}}Veuillez vous reconnecter{{/tr}}</div>
{{/if}}