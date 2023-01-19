{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Tdb.views.filterByText('hospitalisation_tab');
  });
</script>


<table class="tbl" id="hospitalisation_tab">
  <tbody>
  {{foreach from=$listSejours item=_sejour}}
    <tr>
      <td class="text">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_ref_curr_affectation->_guid}}');">
            {{mb_value object=$_sejour->_ref_curr_affectation field=lit_id}}
          </span>
      </td>
      <td class="text">
          <span class="CPatient-view"
                onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_ref_grossesse->_ref_parturiente->_guid}}');">
            {{mb_value object=$_sejour->_ref_grossesse field=parturiente_id}}
          </span>

        {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_sejour->_ref_grossesse->_ref_parturiente}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{mb_value object=$_sejour field=entree}}
          {{if $_sejour->presence_confidentielle}}
            {{mb_include module=planningOp template=inc_badge_sejour_conf}}
          {{/if}}
        </span>
      </td>
      <td style="text-align: center; font-weight: bold">
        {{if $_sejour->_ref_grossesse->_days_relative_acc != ''}}
          J{{$_sejour->_ref_grossesse->_days_relative_acc}}
        {{/if}}

        {{if $_sejour->_ref_grossesse->_ref_dossier_perinat->niveau_alerte_cesar}}
          <div
            {{if $_sejour->_ref_grossesse->_ref_dossier_perinat->niveau_alerte_cesar == 1}}
              class="small-info" style="background-color: lightgreen"
            {{elseif $_sejour->_ref_grossesse->_ref_dossier_perinat->niveau_alerte_cesar == 2}}
              class="small-warning"
            {{elseif $_sejour->_ref_grossesse->_ref_dossier_perinat->niveau_alerte_cesar == 3}}
              class="small-error"
            {{/if}}
          ></div>
        {{/if}}
      </td>
      <td>
        <button type="button" class="edit notext" onclick="Tdb.editSejour('{{$_sejour->_id}}')">{{tr}}CSejour{{/tr}}</button>
        <button type="button" class="soins notext me-tertiary" onclick="Tdb.editD2S('{{$_sejour->_id}}')">{{tr}}dossier_soins{{/tr}}</button>
        {{if $_sejour->_ref_last_operation}}
          <button class="me-tertiary" onclick="Tdb.dossierAccouchement('{{$_sejour->_ref_last_operation->_id}}');" type="button">
            acc
          </button>
        {{/if}}
        <button type="button" class="accouchement_create notext me-tertiary"
                onclick="Tdb.editAccouchement(null, '{{$_sejour->_id}}', '{{$_sejour->_ref_grossesse->_id}}', '')">Accouchement
        </button>
      </td>
      <td class="text">
        {{foreach from=$_sejour->_ref_grossesse->_ref_naissances item=_naissance}}
          <button class="soins notext"
                  onclick="Tdb.editD2S('{{$_naissance->_ref_sejour_enfant->_id}}');">{{tr}}dossier_soins{{/tr}}</button>
          <span class="gender_{{$_naissance->_ref_sejour_enfant->_ref_patient->sexe}}"
                onmouseover="ObjectTooltip.createEx(this, '{{$_naissance->_ref_sejour_enfant->_guid}}');">
                {{$_naissance->_ref_sejour_enfant->_ref_patient}} {{if $_naissance->date_time}}<strong>(J{{$_naissance->_day_relative}})</strong>{{/if}}
              </span>
          <br />
        {{/foreach}}
        </ul>
      </td>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  </tbody>
  <thead>
  <tr>
    <th class="title me-text-align-center" colspan="7">
      <button type="button" class="change notext me-tertiary" onclick="Tdb.views.listHospitalisations(false);" style="float: right;">
        {{tr}}Refresh{{/tr}}
      </button>
      <button class="sejour_create notext" onclick="Tdb.editSejour(null);" style="float: left;">
        {{tr}}CSejour-title-create{{/tr}}
      </button>
      <a onclick="zoomViewport(this);">{{$listSejours|@count}} hospitalisation(s) au {{$date|date_format:$conf.date}}</a>
    </th>
  </tr>
  <tr>
    <th class="narrow">{{mb_title class=CAffectation field=lit_id}}</th>
    <th>{{mb_title class=CGrossesse field=parturiente_id}}</th>
    <th class="narrow">{{mb_title class=CSejour field=entree}}</th>
    <th class="narrow">Acc.</th>
    <th class="narrow">Act. Mère</th>
    <th>{{tr}}CNaissance{{/tr}}</th>
  </tr>
  </thead>
</table>
