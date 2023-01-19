{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<br/>
<form method="POST" onsubmit="return urgencesMaintenance.displaySejour(this)">
  <table class="form">
    <tr>
      <th colspan="2" class="title">Récupération des séjours avec plusieurs RPU</th>
    </tr>
    <tr>
      <th>
        {{tr}}Nb-month{{/tr}}
      </th>
      <td>
        <input type="number" class="num" name="month_maintenance" value="1"/>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
<br/>
<div id="display_sejour"></div>