{{*
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  #legend_resa_planning tr td {
    padding: 10px;
  }
</style>

<table id="legend_resa_planning" class="tbl">
  <tr>
    <th class="category" colspan="2">État / Type d'intervention</th>
  </tr>
  <tr>
    <td style="background: #{{"dPhospi colors comp"|gconf}}; width:50%;"></td><td>{{tr}}CService.type_sejour.comp{{/tr}}</td>
  </tr>
  <tr>
    <td style="background:#{{"dPhospi colors ambu"|gconf}}"></td><td>{{tr}}CService.type_sejour.ambu{{/tr}}</td>
  </tr>
  <tr>
    <td style="background: #{{"dPhospi colors recuse"|gconf}};border-left:solid 3px #3b5aff !important;"></td><td>{{tr}}CSejour.recuse.-1{{/tr}}</td>
  </tr>
  <tr>
    <td style="background:url('images/icons/ray.gif') #23425D!important;" class="hatching"></td><td>Temps pré/post Operatoire</td>
  </tr>
  <tr>
    <td style="background:#{{"dPhospi colors annule"|gconf}}; opacity:0.6" class="hatching"></td><td>{{tr}}Cancelled{{/tr}}</td>
  </tr>
  <tr>
    <th class="category" colspan="2">En/Hors Plage</th>
  </tr>
  <tr>
    <td style="border-right:dotted 3px red"></td><td>Hors plage</td>
  </tr>
  <tr>
    <td style="border-right:dotted 3px #1dff00"></td><td>En plage</td>
  </tr>
</table>