{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  applyContext = function (context_guid) {
    Control.Modal.close();
    Control.Modal.close();
    DocumentV2.addDocument(context_guid, '{{$patient->_id}}');
  };

  Main.add(function () {
    Control.Tabs.create("tabs_context", false);
  });
</script>

<table class="tbl">
  <tr>
    <th>
      <a class="button undo" style="float: left;" onclick="applyContext('{{$patient->_guid}}')">
        Retour au contexte patient
      </a>
      <h3><strong>Choix du contexte</strong></h3>
    </th>
  </tr>
</table>

<table class="main">
  <tr>
    <td style="width: 10%">
      <ul class="control_tabs_vertical" id="tabs_context" style="white-space: nowrap;">
        {{if $patient->_ref_sejours|@count}}
          <li>
            <a href="#context_sejours">
              {{tr}}CSejour{{/tr}}
              ({{$patient->_ref_sejours|@count}})
            </a>
          </li>
        {{/if}}
        {{if $patient->_ref_operations|@count}}
          <li>
            <a href="#context_interventions">
              {{tr}}COperation{{/tr}}
              ({{$patient->_ref_operations|@count}})
            </a>
          </li>
        {{/if}}
        {{if $patient->_ref_consultations|@count}}
          <li>
            <a href="#context_consultations">
              {{tr}}CConsultation{{/tr}}
              ({{$patient->_ref_consultations|@count}})
            </a>
          </li>
        {{/if}}
        {{if $patient->_ref_dossier_medical->_ref_evenements_patient|@count}}
          <li>
            <a href="#context_evenements">
              {{tr}}CEvenementPatient{{/tr}}
              ({{$patient->_ref_dossier_medical->_ref_evenements_patient|@count}})
            </a>
          </li>
        {{/if}}
      </ul>
    </td>
    <td style="width: 90%">
      {{if $patient->_ref_sejours|@count}}
        <div id="context_sejours">
          {{foreach from=$patient->_ref_sejours item=_sejour}}
            <div>
              <button class="tick notext" onclick="applyContext('{{$_sejour->_guid}}')"></button>
              {{$_sejour}}
              &mdash;
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
            </div>
          {{/foreach}}
        </div>
      {{/if}}
      {{if $patient->_ref_operations|@count}}
        <div id="context_interventions">
          {{foreach from=$patient->_ref_operations item=_operation}}
            <div>
              <button class="tick notext" onclick="applyContext('{{$_operation->_guid}}')"></button>
              {{$_operation}}
              &mdash;
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir}}
            </div>
          {{/foreach}}
        </div>
      {{/if}}
      {{if $patient->_ref_consultations|@count}}
        <div id="context_consultations">
          {{foreach from=$patient->_ref_consultations item=_consult}}
            {{foreach from=$_consult->_refs_dossiers_anesth item=_dossier_anesth}}
              <div>
                <button class="tick notext" onclick="applyContext('{{$_dossier_anesth->_guid}}')"></button>
                {{tr}}CConsultation{{/tr}} du {{$_consult->_date|date_format:$conf.date}} à {{$_consult->heure|date_format:$conf.time}}
                &mdash;
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_chir}}
              </div>
              {{foreachelse}}
              <div>
                <button class="tick notext" onclick="applyContext('{{$_consult->_guid}}')"></button>
                {{tr}}CConsultation{{/tr}} du {{$_consult->_date|date_format:$conf.date}} à {{$_consult->heure|date_format:$conf.time}}
                &mdash;
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_chir}}
              </div>
            {{/foreach}}
          {{/foreach}}
        </div>
      {{/if}}
      {{if $patient->_ref_dossier_medical->_ref_evenements_patient|@count}}
        <div id="context_evenements">
          {{foreach from=$patient->_ref_dossier_medical->_ref_evenements_patient item=_evenement}}
            <div>
              <button class="tick notext" onclick="applyContext('{{$_evenement->_guid}}')"></button>
              {{tr}}CEvenementPatient{{/tr}}
              de {{mb_value object=$_evenement field=date}}
            </div>
          {{/foreach}}
        </div>
      {{/if}}
    </td>
  </tr>
</table>