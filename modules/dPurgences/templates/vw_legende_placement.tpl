{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th colspan="2" class="title">L�gende</th>
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
    <td>Prise en charge m�dicale effectu�e</td>
  </tr>
  <tr>
    <td>
      <i class="fa fa-male" style="font-size: 11pt; color: Gold;"></i>
    </td>
    <td>Degr� d'urgence ou motif ccmu renseign�</td>
  </tr>
  <tr>
    <td>
      <i class="fa fa-male" style="font-size: 11pt;border-left:5px solid red;padding-left:1px; color: black;"></i>
    </td>
    <td>Patient ayant un degr� d'urgence : {{tr}}CRPU.ccmu.4{{/tr}} ou {{tr}}CRPU.ccmu.5{{/tr}}</td>
  </tr>
</table>
