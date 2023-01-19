{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=modFSE value="fse"|module_active}}

<script type="text/javascript">
  updatePatient = function (administrative_data) {
    {{if $app->user_prefs.LogicielLectureVitale == 'vitaleVision'}}
    VitaleVision.fillForm(getForm('editFrm'), $V($('modal-beneficiaire-select')), administrative_data);
    {{elseif $modFSE && $modFSE->canRead() && $app->user_prefs.LogicielLectureVitale == 'none'}}
    var url = new Url('dPpatients', 'ajax_update_patient_from_vitale');
    url.addParam('administrative_data', administrative_data);
    url.addParam('patient_id', '{{$patient_id}}');
    url.requestUpdate('systemMsg', {
      onComplete: function () {
        location.reload();
      }
    });
    {{/if}}

    Control.Modal.close();
    return false;
  }

  checkAdministrativeData = function () {
    if ($('administrative_data').checked) {
      $$('.administrative-data-mb').each(function (elt) {
        elt.setStyle({fontWeight: 'normal'});
      });
      $$('.administrative-data-cv').each(function (elt) {
        elt.setStyle({fontWeight: 'bold'});
      });
    } else {
      $$('.administrative-data-mb').each(function (elt) {
        elt.setStyle({fontWeight: 'bold'});
      });
      $$('.administrative-data-cv').each(function (elt) {
        elt.setStyle({fontWeight: 'normal'});
      });
    }
  }

  Main.add(function () {
    {{if $patient_id}}
    checkAdministrativeData();
    {{else}}
    updatePatient(1);
    {{/if}}
  });
</script>

{{if $patient_id}}
  <form name="updatePatientVitale" action="?" method="get" onsubmit="return false;">
    <table class="tbl">
      <tr>
        <th class="title" colspan="3">Mise à jour du patient à partir de la carte Vitale</th>
      </tr>
      <tr class="me-row-valign">
        <td class="halfPane">
          <table class="tbl">
            <tr>
              <th></th>
              <th>Carte Vitale</th>
              <th>Mediboard</th>
            </tr>
            <tr>
              <th class="category" colspan="3">
                Données administratives
                <span style="float: left;">
                <input type="checkbox" id="administrative_data" name="administrative_data" checked="checked"
                       onclick="checkAdministrativeData();" />
              </span>
              </th>
            </tr>
            {{foreach from=$fields.administrative key=_field item=_status}}
              {{mb_ternary test=$_status var=color value="rgba(148, 221, 137, 0.4)" other="rgba(255, 0, 0, 0.40)"}}
              <tr>
                <td>{{mb_label object=$patient_vitale field=$_field}}</td>
                <td style="background-color: {{$color}}"
                    class="administrative-data-cv">{{mb_value object=$patient_vitale field=$_field}}</td>
                <td style="background-color: {{$color}}" class="administrative-data-mb">{{mb_value object=$patient_mb field=$_field}}</td>
              </tr>
            {{/foreach}}
          </table>
        </td>
        <td class="halfPane">
          <table class="tbl">
            <tr>
              <th class="category" colspan="3">Données assuré</th>
            </tr>
            {{foreach from=$fields.assure key=_field item=_status}}
              {{mb_ternary test=$_status var=color value="rgba(148, 221, 137, 0.4)" other="rgba(255, 0, 0, 0.40)"}}
              <tr>
                <td>{{mb_label object=$patient_vitale field=$_field}}</td>
                <td style="background-color: {{$color}}; font-weight: bold;">{{mb_value object=$patient_vitale field=$_field}}</td>
                <td style="background-color: {{$color}}">{{mb_value object=$patient_mb field=$_field}}</td>
              </tr>
            {{/foreach}}
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="3" style="text-align: center;">
          <button class="save" type="submit" onclick="updatePatient(this.form.administrative_data.checked);">{{tr}}Save{{/tr}}</button>
          <button class="cancel" type="button" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
{{/if}}
