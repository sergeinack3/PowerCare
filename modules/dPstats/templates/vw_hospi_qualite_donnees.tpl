{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="2">Qualité de l'information</th>
  </tr>
  <tr>
    <td style="text-align: right;">
      <label title="Nombre total de séjours disponibles selon les filtres utilisés">Séjours disponibles</label>
    </td>
    <td style="width: 100%;">{{$qualite.total}} séjours</td>
  </tr>
  <tr>
    <td style="text-align: right;">
      <label title="Les séjours non placés n'apparaitront pas dans les graphiques 'par service'">Séjours comportant un placement dans
        un lit</label>
    </td>
    <td>{{$qualite.places.total}} séjours ({{$qualite.places.pct|string_format:"%.2f"}} %)</td>
  </tr>
  <tr>
    <td style="text-align: right;">
      <label title="Ce facteur sera pris en compte selon le type de données choisi">Séjours comportant une entrée et une sortie
        réelle</label>
    </td>
    <td>{{$qualite.reels.total}} séjours ({{$qualite.reels.pct|string_format:"%.2f"}} %)</td>
  </tr>
</table>