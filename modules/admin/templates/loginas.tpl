{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if (!$conf.admin.LDAP.ldap_connection || $conf.admin.LDAP.allow_login_as_admin) && $app->user_type == 1 && ($app->user_id != $loginas_user->_id) && !$loginas_user->template}}
<form name="loginas-{{$loginas_user->_id}}" method="post" onsubmit="return UserSwitch.login(this, 'systemMsg');">
  <input type="hidden" name="username" value="{{$loginas_user->user_username}}" />
  <button type="submit" class="tick compact">
    {{tr}}Substitute{{/tr}}
  </button>
</form>
{{/if}}
