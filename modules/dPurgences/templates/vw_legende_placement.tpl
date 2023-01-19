{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th colspan="2" class="title">Légende</th>
  </tr>
  <tr>
    <th colspan="2">Couleur de silouhette</th>
  </tr>
  <tr>
    <td>
      <i class="fa fa-male" style="font-size: 11pt; color: gray;"></i>
    </td>
    <td>Patient non pris en charge</td>
  </tr>
  <tr>
    <td>
      <i class="fa fa-male" style="font-size: 11pt; color: blue;"></i>
    </td>
    <td>Prise en charge médicale effectuée</td>
  </tr>
  <tr>
    <td>
      <i class="fa fa-male" style="font-size: 11pt; color: Gold;"></i>
    </td>
    <td>Degré d'urgence ou motif ccmu renseigné</td>
  </tr>
  <tr>
    <td>
      <i class="fa fa-male" style="font-size: 11pt;border-left:5px solid red;padding-left:1px; color: black;"></i>
    </td>
    <td>Patient ayant un degré d'urgence : {{tr}}CRPU.ccmu.4{{/tr}} ou {{tr}}CRPU.ccmu.5{{/tr}}</td>
  </tr>
</table>
