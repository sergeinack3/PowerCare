{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="2">Urgences</th>
  </tr>
  {{if "dPurgences CRPU type_sejour"|gconf === "urg_consult"}}
    <tr>
      <th class="section" colspan="2">{{tr}}CRPU-reconvoc|pl{{/tr}}</th>
    </tr>
    <tr>
      <th>Couleur</th>
      <th>Description</th>
    </tr>
    <tr>
      <td style="background-color:#9F0"></td>
      <td class="text">{{tr}}CConsultation.chrono.32{{/tr}}</td>
    </tr>
  {{/if}}
  <tr>
    <th class="section" colspan="2">{{tr}}Main_courante{{/tr}}</th>
  </tr>
  <tr>
    <th>Couleur</th>
    <th>Description</th>
  </tr>
  <tr>
    <td></td>
    <td class="text">Patient non pris en charge</td>
  </tr>
  <tr>
    <td style="background-color:#ccf"></td>
    <td class="text">Patient pris en charge</td>
  </tr>
  <tr>
    <td style="border-right: 5px solid black"></td>
    <td class="text">Patient sorti</td>
  </tr>
  <tr>
    <th colspan="2">Degrés d'urgences</th>
  </tr>
  <tr>
    <th class="section" colspan="2">{{tr}}CRPU-ccmu{{/tr}}</th>
  </tr>
  <tr>
    <th>Couleur</th>
    <th>Description</th>
  </tr>
  <tr>
    <td style="background-color:#{{"dPurgences Display color_ccmu_1"|gconf}}"></td>
    <td class="text">{{tr}}CRPU.ccmu.1.desc{{/tr}}</td>
  </tr>
  {{if $conf.ref_pays == 1}}
  <tr>
    <td style="background-color:#{{"dPurgences Display color_ccmu_P"|gconf}}"></td>
    <td class="text">{{tr}}CRPU.ccmu.P.desc{{/tr}}</td>
  </tr>
  {{/if}}
  <tr>
    <td style="background-color:#{{"dPurgences Display color_ccmu_2"|gconf}}"></td>
    <td class="text">{{tr}}CRPU.ccmu.2.desc{{/tr}}</td>
  </tr>
  <tr>
    <td style="background-color:#{{"dPurgences Display color_ccmu_3"|gconf}}"></td>
    <td class="text">{{tr}}CRPU.ccmu.3.desc{{/tr}}</td>
  </tr>  
  <tr>
    <td style="background-color:#{{"dPurgences Display color_ccmu_4"|gconf}}"></td>
    <td class="text">{{tr}}CRPU.ccmu.4.desc{{/tr}}</td>
  </tr>
  {{if $conf.ref_pays == 1}}
  <tr>
    <td style="background-color:#{{"dPurgences Display color_ccmu_5"|gconf}}"></td>
    <td class="text">{{tr}}CRPU.ccmu.5.desc{{/tr}}</td>
  </tr>
  <tr>
    <td style="background-color:#{{"dPurgences Display color_ccmu_D"|gconf}}"></td>
    <td class="text">{{tr}}CRPU.ccmu.D.desc{{/tr}}</td>
  </tr>
  {{/if}}
  {{if "dPurgences Display display_cimu"|gconf}}
    <tr>
      <th class="section" colspan="2">{{tr}}CRPU-cimu{{/tr}}</th>
    </tr>
    <tr>
      <th>Couleur</th>
      <th>Description</th>
    </tr>
    <tr>
      <td style="background-color:#{{"dPurgences Display color_cimu_5"|gconf}}"></td>
      <td class="text">{{tr}}CRPU.cimu.5{{/tr}}</td>
    </tr>
    <tr>
      <td style="background-color:#{{"dPurgences Display color_cimu_4"|gconf}}"></td>
      <td class="text">{{tr}}CRPU.cimu.4{{/tr}}</td>
    </tr>
    <tr>
      <td style="background-color:#{{"dPurgences Display color_cimu_3"|gconf}}"></td>
      <td class="text">{{tr}}CRPU.cimu.3{{/tr}}</td>
    </tr>
    <tr>
      <td style="background-color:#{{"dPurgences Display color_cimu_2"|gconf}}"></td>
      <td class="text">{{tr}}CRPU.cimu.2{{/tr}}</td>
    </tr>
    <tr>
      <td style="background-color:#{{"dPurgences Display color_cimu_1"|gconf}}"></td>
      <td class="text">{{tr}}CRPU.cimu.1{{/tr}}</td>
    </tr>
  {{/if}}
  <tr>
    <th colspan="2">{{tr}}CRPU-_attente{{/tr}}</th>
  </tr>
  <tr>
    <th>Image</th>
    <th>Description</th>
  </tr>
  <tr>
    <td>
      <span class="me-attente-part me-attente-part-first">
        <img src="images/icons/attente_first_part.png" />
      </span>
    </td>
    <td>
      Attente depuis moins de {{$conf.dPurgences.attente_first_part|date_format:$conf.time}}
    </td>
  </tr>
  <tr>
    <td>
      <span class="me-attente-part me-attente-part-second">
        <img src="images/icons/attente_second_part.png" />
      </span>
    </td>
    <td>
      Attente entre {{$conf.dPurgences.attente_first_part|date_format:$conf.time}} et {{$conf.dPurgences.attente_second_part|date_format:$conf.time}}
    </td>
  </tr>
  <tr>
    <td>
      <span class="me-attente-part me-attente-part-third">
        <img src="images/icons/attente_third_part.png" />
      </span>
    </td>
    <td>
      Attente entre {{$conf.dPurgences.attente_second_part|date_format:$conf.time}} et {{$conf.dPurgences.attente_third_part|date_format:$conf.time}}
    </td>
  </tr>
  <tr>
    <td>
      <span class="me-attente-part me-attente-part-fourth">
        <img src="images/icons/attente_fourth_part.png" />
      </span>
    </td>
    <td>
      Attente de plus de {{$conf.dPurgences.attente_third_part|date_format:$conf.time}}
    </td>
  </tr>
</table>
