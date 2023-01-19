{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    getForm("user_auth").onsubmit();
  });

  changeAuthPage = function(start) {
    var form = getForm("user_auth");
    $V(form.elements.start, start);
    form.onsubmit();
  }
</script>

{{if $ua->_id}}
  <h3 onmouseover="ObjectTooltip.createEx(this, '{{$ua->_guid}}')">{{$ua}}</h3>
{{/if}}


<form name="user_auth" method="get" action="" onsubmit="return onSubmitFormAjax(this, null, 'auth_results');">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="ajax_search_user_authentications" />
  <input type="hidden" name="start" value="{{$start}}" />
  <input type="hidden" name="user_agent_id" value="{{$user_agent_id}}" />
  <input type="hidden" name="user_id" value="{{$user_id}}" />
  <input type="hidden" name="date_min" value="{{$date_min}}" />
  <input type="hidden" name="date_max" value="{{$date_max}}" />
</form>

<div id="auth_results"></div>
