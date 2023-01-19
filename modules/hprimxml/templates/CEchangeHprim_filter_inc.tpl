{{*
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th>{{mb_label object=$exchange field=statut_acquittement}}</th>
  <td colspan="3">
    <select class="str" name="statut_acquittement" onchange="$V(this.form.page, 0)">
      <option value="">&mdash; Liste des statuts &mdash;</option>
      <option value="OK">Ok</option>
      <option value="avertissement">Avertissement </option>
      <option value="erreur">Erreur</option>
    </select>
  </td>
</tr>