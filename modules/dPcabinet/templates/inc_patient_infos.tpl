{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=vertical value=0}}
{{mb_default var=show_btn_dossier_complet value=true}}

<script>
  showSummary = function(patient_id) {
    var url = new Url('cabinet', 'vw_resume');
    url.addParam("patient_id", patient_id);
    url.popup(800, 500, 'Summary' + (Preferences.multi_popups_resume == '1' ? patient_id : null));
  }
</script>

{{if $show_btn_dossier_complet}}
  <!-- Dossier complet -->
  <a class="button search me-secondary me-margin-2" href="{{$patient->_dossier_cabinet_url}}">
    {{tr}}dPpatients-CPatient-Dossier_complet{{/tr}}
  </a>
  {{if $vertical}}
    <br/>
  {{/if}}
{{/if}}

<!-- Dossier résumé -->
<button class="search me-secondary me-margin-2" onclick="showSummary('{{$patient->_id}}')">
  {{tr}}common-Summary{{/tr}}
</button>
