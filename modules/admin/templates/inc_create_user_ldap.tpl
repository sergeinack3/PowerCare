{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  window.ldap_user_id='{{$user->_id}}'; 
  window.ldap_user_actif='{{$user->_user_actif}}';
  window.ldap_user_deb_activite='{{$user->_user_deb_activite}}'; 
  window.ldap_user_fin_activite='{{$user->_user_fin_activite}}'; 
  window.no_association='{{$association}}';
</script>