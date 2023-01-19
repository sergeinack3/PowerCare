{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="7">Liste des séjours ({{$date}})</th>
  </tr>
  <tr>
    <th>Nom / Prénom</th>
    <th>Entrée</th>
    <th>Sortie</th>
    <th>Entrée <br /> (bornée sur le mois)</th>
    <th>Sortie <br /> (bornée sur le mois)</th>
    <th class="text">Nombre de jours-patient <br /> sur le mois</th>
  </tr>
  {{foreach from=$results item=_result}}
    <tr>
      <td class="text">
        {{$_result.nom}} {{$_result.prenom}}
      </td>
      <td>
        {{$_result.entree|date_format:$conf.datetime}}
      </td>
      <td>
        {{$_result.sortie|date_format:$conf.datetime}}
      </td>
      <td>
        {{$_result.entree_bornee|date_format:$conf.date}}
      </td>
      <td>
        {{$_result.sortie_bornee|date_format:$conf.date}}
      </td>
      <td>
        {{$_result.nb_jours}}
      </td>
    </tr>
  {{/foreach}}
</table>