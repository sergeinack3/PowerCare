{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function openResume(patient_id) {
    var fiche = $('resume_'+patient_id);
    if (fiche) {
      Modal.open(fiche, {showClose: true, width:800, height: 900});
    }
  }

  function openPlage(plage_id) {
    var plage = $('plage_'+plage_id);
    if (plage) {
      Modal.open(plage, {showClose: true, width:800, height: 900});
    }
  }
</script>

<table class="tbl">
  <tr>
    <th class="title" colspan="6">{{tr}}CConsultation{{/tr}}s du {{$date|date_format:$conf.longdate}} : {{$nbConsultations}} {{tr}}CConsultation{{/tr}}s</th>
  </tr>
  <tr>
    <th>{{tr}}CMediusers{{/tr}}</th>
    <td colspan="5" class="text">
      {{foreach from=$praticiens item=_prat}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat}}
      {{/foreach}}
    </td>
  </tr>
  <tr>
    <th style="width: 100px;">Début</th>
    <th style="width: 100px;">Fin</th>
    <th style="width: 200px;">Plages de consultations</th>
    <th>Motifs</th>
    <th>Taux d'occupation</th>
    <th>Consultations</th>
  </tr>
  {{foreach from=$plages item=_plage_consultation}}
    {{assign var=nbConsult value=$_plage_consultation->_ref_consultations|@count}}
    <tr>
      <td>{{$_plage_consultation->debut|date_format:$conf.time}}</td>
      <td>{{$_plage_consultation->fin|date_format:$conf.time}}</td>
      <td>{{$_plage_consultation->_ref_chir}}</td>
      <td>{{$_plage_consultation->libelle}}</td>
      <td>
        <div class="progressBar">
          <div class="bar" style="width: {{if $_plage_consultation->_fill_rate > 100}}100{{else}}{{$_plage_consultation->_fill_rate}}{{/if}}%; background: #abe;" >
            <div class="text" style="color: black; text-shadow: 1px 1px 2px white;">{{$_plage_consultation->_fill_rate}}%</div>
          </div>
        </div>
      </td>
      <td {{if !$nbConsult}}class="empty"{{/if}}>
        {{if $nbConsult}}
          <button class="pagelayout button" onclick="openPlage('{{$_plage_consultation->_id}}')">Voir la liste ({{$_plage_consultation->_nb_patients}})</button>
          <table class="tbl" id="plage_{{$_plage_consultation->_id}}" style="display: none;">
            <tr>
              <th colspan="6" class="title">{{$_plage_consultation->_view}}</th>
            </tr>
            <tr>
              <th rowspan="{{$nbConsult+1}}">{{$nbConsult}} {{tr}}CConsultation{{/tr}}{{if $nbConsult>1}}s{{/if}}</th>
              <th>Entrée</th>
              <th>Patient</th>
              <th>Age</th>
              <th>Motif</th>
              <th>Remarques</th>
            </tr>
            {{foreach from=$_plage_consultation->_ref_consultations item=_consultation}}
              <tr {{if !$_consultation->patient_id}}class="hatching"{{/if}}>
                <td>{{$_consultation->heure|date_format:$conf.time}}</td>
                <td>
                  {{if $_consultation->patient_id}}
                    <a href="#" onclick="openResume('{{$_consultation->_ref_patient->_id}}')">
                    {{$_consultation->_ref_patient}}
                    </a>
                  {{elseif $_consultation->groupee && $_consultation->no_patient}}
                    [{{tr}}CConsultation-MEETING{{/tr}}]
                  {{else}}
                    [{{tr}}CConsultation-PAUSE{{/tr}}]
                  {{/if}}
                </td>
                <td>
                  {{if $_consultation->patient_id}}
                    {{mb_value object=$_consultation->_ref_patient field=_age}}
                  {{/if}}
                </td>
                <td>{{$_consultation->motif}}</td>
                <td>{{$_consultation->rques}}</td>
              </tr>
            {{/foreach}}
          </table>
        {{else}}
          {{tr}}CConsultation.none{{/tr}}
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CPlageconsult.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

<!-- dossiers patients résumés -->
{{foreach from=$resumes_patient key=patient_id item=_patient}}
  <div id="resume_{{$patient_id}}" style="display: none;">{{$_patient|smarty:nodefaults}}</div>
{{/foreach}}
