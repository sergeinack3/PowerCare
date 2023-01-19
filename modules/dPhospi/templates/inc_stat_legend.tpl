{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl" style="width: auto !important;">
  <tr>
    <th>
      Disponibles
    </th>
    <td class="text">
      % du nombre de lits disponibles sur le nombre de lits total du service.<br />
      Un lit n'est pas ouvert s'il y a un blocage à minuit.
    </td>
  </tr>
  <tr>
    <th>
      Prévu
    </th>
    <td class="text">
      % du nombre de lits occupés (p/r au nombre de lits du service) de manière prévisionnelle à minuit.<br />
      On considère prévu un séjour sectorisé dans un service.
    </td>
  </tr>
  <tr>
    <th>
      Affectés
    </th>
    <td class="text">
      % du nombre de lits réellement occupés (p/r au nombre de lits du service),<br />
      c'est à dire ayant un placement dans un lit ou dans le couloir du service à minuit
    </td>
  </tr>
  <tr>
    <th>
      Entrées
    </th>
    <td>
      % du nombre d'entrée dans le journée (p/r au nombre de lits du service). <br />
      Utile pour connaitre le taux d'activité pour les services d'ambulatoire.
    </td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
    </td>
  </tr>
</table>