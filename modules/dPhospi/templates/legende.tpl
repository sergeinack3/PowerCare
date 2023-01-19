{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title">Icone</th>
    <th class="title">Description</th>
  </tr>
  <tr>
    <th colspan="2">Etat des patients</th>
  </tr>
  <tr>
    <td style="text-align: right;">
      {{mb_include module=hospi template=inc_vw_icone_sejour lettre="X"}}
    </td>
    <td class="text">{{tr}}dPhospi-legend-ambu-leaving-tonight{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;">
      {{mb_include module=hospi template=inc_vw_icone_sejour lettre="O"}}
    </td>
    <td class="text">{{tr}}dPhospi-legend-hospi-leaving-tomorrow{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;">
      {{mb_include module=hospi template=inc_vw_icone_sejour lettre="Oo"}}
    </td>
    <td class="text">{{tr}}dPhospi-legend-hospi-leaving-today{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;">
      {{mb_include module=hospi template=inc_vw_icone_sejour lettre="OC"}}
    </td>
    <td class="text">{{tr}}dPhospi-legend-hospi-moved-tomorrow{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;">
      {{mb_include module=hospi template=inc_vw_icone_sejour lettre="OoC"}}
    </td>
    <td class="text">{{tr}}dPhospi-legend-hospi-moved-today{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: center;">
      <span class="sortie_transfert" style="float: right;" title="{{tr}}CSejour.sortie_transfert{{/tr}}">
        {{tr}}CSejour.sortie_transfert.court{{/tr}}
      </span>
    </td>
    <td class="text">{{tr}}CSejour.sortie_transfert{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: center;">
      <div class="icone_nc" style="float: right; height: 12px;"
           title="{{tr}}CSejour-nuit_convenance-desc{{/tr}}">{{tr}}CSejour-nuit_convenance-court{{/tr}}</div>
    </td>
    <td class="text">{{tr}}CSejour-nuit_convenance-desc{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right; font-weight: bold;" class="septique">M. X y</td>
    <td class="text">{{tr}}dPhospi-legend-patient-sceptic{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right; font-weight: bold;" class="patient-not-arrived">M. X y 27/03 17h00</td>
    <td class="text">{{tr var1="17h00" var2="27/03"}}dPhospi-legend-patient-arriving-time%s-date%s{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;"><strong>M. X y</strong></td>
    <td class="text">{{tr}}dPhospi-legend-patient-present{{/tr}}</td>
  </tr>
  <tr>
    <td class="hatching" style="text-align: right; font-weight: bold;">M. X y</td>
    <td class="text">{{tr}}CPatient-Patient with medical release permission{{/tr}}</td>
  </tr>

  {{mb_include module=hospi template=inc_legend_bmr_bhre}}

  <tr>
    <th colspan="2">Alertes</th>
  </tr>
  <tr>
    <td style="text-align: right;"><img src="modules/dPhospi/images/double.png" name="chambre double possible" /></td>
    <td class="text">{{tr}}dPhospi-legend-double-bed-possible{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;"><img src="modules/dPhospi/images/seul.png" name="chambre simple obligatoire" /></td>
    <td class="text">{{tr}}dPhospi-legend-simple-bed-only{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;"><img src="modules/dPhospi/images/surb.png" name="collision" /></td>
    <td class="text">{{tr}}dPhospi-legend-collision-double-patient-one-bed{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;"><img src="modules/dPhospi/images/sexe.png" name="conflit de sexe" /></td>
    <td class="text">{{tr}}dPhospi-legend-men-woman-same-room{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;"><img src="modules/dPhospi/images/age.png" name="ecart d'age important" /></td>
    <td class="text">{{tr}}dPhospi-legend-age-superior-15-years-old{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;"><img src="modules/dPhospi/images/prat.png" name="conflit de praticiens" /></td>
    <td class="text">{{tr}}dPhospi-legend-conflict-praticien-2patient-2prat-same-speciality{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;"><img src="modules/dPhospi/images/path.png" name="conflit de pathologie" /></td>
    <td class="text">{{tr}}dPhospi-legend-uncompatible-pathology-same-room{{/tr}}</td>
  </tr>
  <tr>
    <td style="text-align: right;"><img src="modules/dPhospi/images/annule.png" name="Chambre plus utilisée" /></td>
    <td class="text">{{tr}}dPhospi-legend-room-abandonned{{/tr}}</td>
  </tr>
  {{if 'hotellerie'|module_active}}
    {{mb_include module=hotellerie template=vw_legende included=true}}
  {{/if}}
  <tr>
    <td colspan="2" class="button">
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
    </td>
  </tr>
</table>