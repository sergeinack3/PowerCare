{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  La recherche se fera sur les annuaires de l'établissement : <strong>{{$current_group}}</strong>
</div>

<form name="listCriteresRechercheLDAP" action="?" method="get" onsubmit="return Url.update(this, 'search-results');">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="a" value="ajax_choose_filter_ldap" />
  <table class="form">
    <tr>
      <th class="title" colspan="6">{{tr}}CLDAP_search_criteria{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_title class="CUser" field="user_username"}}</th>
      <td>
        <input type="text" name="user_username" value="" />
      </td>
      <th>{{mb_title class="CUser" field="user_first_name"}}</th>
      <td>
        <input type="text" name="user_first_name" value="" />
      </td>
      <th>{{mb_title class="CUser" field="user_last_name"}}</th>
      <td>
        <input type="text" name="user_last_name" value="" />
      </td>
    </tr>
    <tr>
      <td colspan="6" style="text-align: center">
        <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="search-results"></div>