{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="2">Qualit� de l'information</th>
  </tr>
  <tr>
    <td style="text-align: right;">
      <label title="Nombre total de s�jours disponibles selon les filtres utilis�s">S�jours disponibles</label>
    </td>
    <td style="width: 100%;">{{$qualite.total}} s�jours</td>
  </tr>
  <tr>
    <td style="text-align: right;">
      <label title="Les s�jours non plac�s n'apparaitront pas dans les graphiques 'par service'">S�jours comportant un placement dans
        un lit</label>
    </td>
    <td>{{$qualite.places.total}} s�jours ({{$qualite.places.pct|string_format:"%.2f"}} %)</td>
  </tr>
  <tr>
    <td style="text-align: right;">
      <label title="Ce facteur sera pris en compte selon le type de donn�es choisi">S�jours comportant une entr�e et une sortie
        r�elle</label>
    </td>
    <td>{{$qualite.reels.total}} s�jours ({{$qualite.reels.pct|string_format:"%.2f"}} %)</td>
  </tr>
</table>