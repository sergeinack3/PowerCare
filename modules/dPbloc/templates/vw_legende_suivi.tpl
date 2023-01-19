{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="2" class="title">L�gende</th>
  </tr>
  <tr>
    <th>Couleur</th>
    <th>Description</th>
  </tr>
  <tr>
    <th colspan="2" class="section">Etiquette patient</th>
  </tr>
  <tr>
    <td style="border-left: 8px solid #68c;"></td>
    <td class="text">Couleur de la fonction du praticien</td>
  </tr>
  <tr>
    <td style="background-color:#eef"></td>
    <td class="text">Patient de sexe masculin</td>
  </tr>
  <tr>
    <td style="background-color:#fee"></td>
    <td class="text">Patient de sexe f�minin</td>
  </tr>
  <tr>
    <td style="border: 2px solid red;"></td>
    <td class="text">{{tr}}COperation-emergency{{/tr}}</td>
  </tr>
  <tr>
    <td>
      <i class="fas fa-cut event-icon"
         style="float:right;background-color:grey; font-size: 100%;"></i>
    </td>
    <td class="text">Intervention non d�but�e</td>
  </tr>
  <tr>
    <td>
      <i class="fas fa-cut event-icon"
         style="float:right;background-color:blueviolet; font-size: 100%;"></i>
    </td>
    <td class="text">Intervention d�but�e</td>
  </tr>
  <tr>
    <td>
      <i class="fas fa-cut event-icon"
         style="float:right;background-color:steelblue; font-size: 100%;"></i>
    </td>
    <td class="text">Intervention termin�e</td>
  </tr>
  <tr>
    <th colspan="2" class="section">Ligne d'�volution</th>
  </tr>
  <tr>
    <td style="background-color: green"></td>
    <td class="text">Timing renseign�</td>
  </tr>
  <tr>
    <td style="background-color:white"></td>
    <td class="text">Timing non renseign�</td>
  </tr>
</table>