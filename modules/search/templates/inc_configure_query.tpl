{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<form method="get" name="requestSearch" action="?m=search" class="watched prepared" onsubmit="return Search.requestCluster(this);">
  <table class="main tbl">
    <tr>
      <th class="category" colspan="3">
        <span>Ex�cuter une requ�te http</span>
      </th>
    </tr>
    <tr>
      <td>
        <label><input type="radio" name="type_request" value="get" checked /> GET</label>
        <label><input type="radio" name="type_request" value="post" /> POST</label>
        <label><input type="radio" name="type_request" value="put" /> PUT</label>
      </td>
    </tr>
    <tr>
      <td>
        <label><textarea name="request" id="request">{{$racine_elastic}}</textarea></label>
      </td>
    </tr>
    <tr>
      <td class="button">
        <button class="new" type="submit">Effectuer la requ�te</button>
      </td>
    </tr>
  </table>
</form>