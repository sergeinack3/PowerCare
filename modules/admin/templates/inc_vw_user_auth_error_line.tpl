{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <td style="text-align: center;">
    {{mb_ditto name=date value=$_user_auth->datetime|date_format:$conf.date}}
  </td>
  
  <td style="text-align: center;">
    {{mb_ditto name=time value=$_user_auth->datetime|date_format:$conf.time}}
  </td>
  
  <td>
    {{if $_user_auth->user_id}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user_auth->_ref_user->_ref_mediuser}}
    {{/if}}
  </td>

  <td>
    {{mb_value object=$_user_auth field=login_value}}
  </td>
  
  <td>
    {{mb_value object=$_user_auth field=auth_method}}
  </td>
  
  <td>
    {{mb_value object=$_user_auth field=ip_address}}
  </td>

  <td class="narrow">
    <tt>{{$_user_auth->identifier}}</tt>
  </td>

  <td class="text compact">
    {{$_user_auth->message|smarty:nodefaults|purify}}
  </td>
</tr>
