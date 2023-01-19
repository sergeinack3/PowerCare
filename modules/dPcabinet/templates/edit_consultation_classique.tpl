{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=auto_refresh_frequency value="dPcabinet CConsultation auto_refresh_frequency"|gconf}}

{{if "dPprescription"|module_active}}
  {{mb_script module="dPprescription" script="prescription"}}
  {{mb_script module="dPprescription" script="prescription_editor"}}
{{/if}}

{{mb_script module="compteRendu" script="document"}}
{{mb_script module="compteRendu" script="modele_selector"}}
{{mb_script module="cabinet" script="edit_consultation"}}

<script>
  {{if !$consult->_canEdit}}
    App.readonly = true;
  {{/if}}

  function printAllDocs() {
    var url = new Url("cabinet", "print_select_docs");
    url.addParam('consultation_id', '{{$consult->_id}}');
    url.popup(700, 500, "printDocuments");
  }

  function submitAll() {
    $$('form.editFrmExam').each(function(_form) {
      onSubmitFormAjax(_form);
    });
  }

  Main.add(function () {
    ListConsults.init("{{$consult->_id}}", "{{$userSel->_id}}", "{{$date}}", "{{$vue}}", "{{$current_m}}", "{{$auto_refresh_frequency}}");
  } );
</script>


<table class="main">
  <tr>
    <td id="listConsultToggle" style="width: 240px;">
      <div id="listConsult"></div>
    </td>
    <td>
      {{if $consult->_id}}
        {{assign var="patient" value=$consult->_ref_patient}}

        <div id="finishBanner">
          {{mb_include module=cabinet template=inc_finish_banner}}
        </div>

        <div id="Infos">
          {{mb_include module=cabinet template=inc_patient_infos_accord_consult}}
        </div>

        <div id="mainConsult">
          {{mb_include module=cabinet template=inc_main_consultform}}
        </div>

        <div id="fdrConsult">
          {{mb_include module=cabinet template=inc_fdr_consult}}
        </div>

        <!-- Reglement -->
        {{mb_script module="cabinet" script="reglement"}}
        <script>
          Reglement.consultation_id = '{{$consult->_id}}';
          Reglement.user_id = '{{$userSel->_id}}';
          Reglement.register(true);
        </script>
      
      {{/if}}
    </td>
  </tr>
</table>
