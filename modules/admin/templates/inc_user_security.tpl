{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$user || !$user->_id}}
  {{mb_return}}
{{/if}}

<div id="user-kerberos-security">
  {{mb_include module=admin template=inc_user_kerberos_security user=$user}}
</div>
