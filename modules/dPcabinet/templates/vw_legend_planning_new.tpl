{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  #legendPlanningCabinetNew td.color {
    border:solid 1px black!important;
  }
</style>

<table class="tbl" id="legendPlanningCabinetNew">
  <tbody>
    <tr>
      <th class="title" style="width:10%;">Plage</th>
      <th class="title" style="width: 40%:">Cr�neaux</th>
      <th class="title" style="width: 50%">{{tr}}Legend{{/tr}}</th>
    </tr>
    <tr>
      <th class="category" colspan="3">{{tr}}CPlageconsult{{/tr}}</th>
    </tr>
    <tr>
      <td></td>
      <td style="background: #cfc;" class="color"></td>
      <td>Cr�neau Libre</td>
    </tr>
    <tr>
      <td></td>
      <td style="background: #fee;" class="color"></td>
      <td>Cr�neau r�serv�</td>
    </tr>
    <tr>
      <td></td>
      <td style="background: #faa;" class="color"></td>
      <td>Premi�re consultation</td>
    </tr>
    <tr>
      <td></td>
      <td style="background: #faf;" class="color"></td>
      <td>Derni�re consultation</td>
    </tr>
    <tr>
      <td></td>
      <td style="background: #a7a3a3;" class="color"></td>
      <td>Pause</td>
    </tr>
    <tr>
      <td></td>
      <td style="background: #3E9DF4;" class="color"></td>
      <td>est remplac� par un autre praticien</td>
    </tr>
    <tr>
      <td></td>
      <td style="background: #fda;" class="color"></td>
      <td>Remplace un autre praticien</td>
    </tr>
    {{if $view_operations}}
      <tr>
        <th colspan="3" class="category">Plages {{tr}}COperation{{/tr}}</th>
      </tr>
      <tr>
        <td style="background: #3c75ea;" class="color"></td>
        <td></td>
        <td>Intervention hors plage</td>
      </tr>
      <tr>
        <td style="background: #bbccee;" class="color"></td>
        <td></td>
        <td>Plage op�ratoire</td>
      </tr>
      <tr>
        <td class="color">Autre</td>
        <td></td>
        <td>Plage de consultation</td>
      </tr>
    {{/if}}
  </tbody>
</table>