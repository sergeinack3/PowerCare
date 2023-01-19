{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=only_consult value=false}}

{{assign var=patient value=$consult->_ref_patient}}
{{assign var=praticien value=$consult->_ref_chir}}

<script>
  updateCountTab = function() {
    if (Preferences.MODCONSULT != "1") {
      return;
    }
    var count_items = 0;
    var items = ["documents-fdr", "files-fdr", "documents-CSejour", "files-CSejour"];
    for (i in items) {
      var item = items[i];
      if (Object.isFunction(item)) {
        continue;
      }
      var elt = $(item);
      if (elt) {
        count_items += elt.select(".docitem").length;
      }
    }

    var button_documents = $('button_documents');
    if (button_documents && button_documents.down('span')) {
      button_documents.down('span').innerHTML = count_items;
    }

    var tab_presc = $("prescription-CConsultation-fdr");
    if (tab_presc && tab_presc.down("table")) {
      count_items++;
    }

    Control.Tabs.setTabCount("fdrConsult", count_items);
  }
</script>

{{mb_include module=patients template=inc_button_vue_globale_docs patient_id=$patient->_id object=$patient
show_send_mail=1 show_telemis=1}}

{{if $consult->_ref_patient && "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
  <div class="me-text-align-center">
      {{mb_include module=appFineClient template=inc_show_sas_tabs tabs="order|received" patient=$consult->_ref_patient count=$count_order}}
      {{mb_include module=appFineClient template=inc_acces_send_order patient=$consult->_ref_patient}}
  </div>
{{/if}}

<table class="form me-no-box-shadow">
  <tr>
    {{assign var=object value=$consult}}
    {{if $consult->_ref_consult_anesth && $consult->_ref_consult_anesth->_id}}
      {{assign var=object value=$consult->_ref_consult_anesth}}
    {{/if}}

    <td class="halfPane me-valign-top">
      <fieldset>
        <legend>{{tr}}CFile{{/tr}} - {{tr}}{{$object->_class}}{{/tr}}</legend>
        <div id="files-fdr">
          {{mb_script module=files script=file ajax=true}}
          <script>
            File.use_mozaic = 1;
            File.register('{{$object->_id}}','{{$object->_class}}', 'files-fdr');
          </script>
        </div>
      </fieldset>
    </td>
    <td class="halfPane me-valign-top">
      <fieldset>
        <legend>{{tr}}CCompteRendu{{/tr}} - {{tr}}{{$object->_class}}{{/tr}}</legend>
        <div id="documents-fdr">
          {{mb_script module=compteRendu script=document ajax=true}}
          {{mb_script module=compteRendu script=modele_selector ajax=true}}
          <script>
            Document.register('{{$object->_id}}','{{$object->_class}}','{{$consult->_praticien_id}}','documents-fdr');
          </script>
        </div>
      </fieldset>
    </td>
  </tr>
  {{if $consult->sejour_id || ($object->_class == 'CConsultAnesth' && $object->sejour_id)}} {{* Cas d'un RPU *}}
    {{if $object->_class == 'CConsultAnesth'}}
      {{assign var=sejour value=$object->_ref_sejour}}
    {{else}}
      {{assign var=sejour value=$consult->_ref_sejour}}
    {{/if}}
    <tr>
      <td class="halfPane me-valign-top">
        <fieldset>
          <legend>{{tr}}CFile{{/tr}} - {{tr}}CSejour{{/tr}}</legend>
          <div id="files-CSejour">
            <script>
              File.register('{{$sejour->_id}}','{{$sejour->_class}}', 'files-CSejour');
            </script>
          </div>
        </fieldset>
      </td>
      <td class="halfPane me-valign-top">
        <fieldset>
          <legend>{{tr}}CCompteRendu{{/tr}} - {{tr}}CSejour{{/tr}}</legend>
          <div id="documents-CSejour">
            <script>
              Document.register('{{$sejour->_id}}','{{$sejour->_class}}','{{$sejour->_praticien_id}}','documents-CSejour');
            </script>
          </div>
        </fieldset>
      </td>
    </tr>
  {{/if}}
  {{if !$only_consult}}
    <tr>
      <td class="halfPane me-valign-top">
        {{if "dPprescription"|module_active && "dPcabinet CPrescription view_prescription_externe"|gconf}}
          {{mb_script module=prescription script=prescription ajax=true}}
         {{mb_script module=prescription script=prescription_editor ajax=true}}
        <fieldset>
          <legend>{{tr}}CPrescription{{/tr}}</legend>
          <div id="prescription_register">
            <script>
              PrescriptionEditor.register('{{$consult->_id}}','{{$consult->_class}}','fdr','{{$consult->_praticien_id}}');
            </script>
          </div>
        </fieldset>
        {{/if}}
      </td>
      <td class="halfPane me-valign-top">
        <fieldset>
          <legend>{{tr}}CDevisCodage{{/tr}}</legend>
          {{mb_script module=ccam script=DevisCodage ajax=1}}
          <script>
            Main.add(function() {
              DevisCodage.list('{{$consult->_class}}', '{{$consult->_id}}');
            });
          </script>
          <div id="view-devis"></div>
        </fieldset>
      </td>
    </tr>
  {{/if}}
  {{if 'fse'|module_active && 'oxPyxvital'|module_active && $app->user_prefs.LogicielFSE == "oxPyxvital"}}
    {{mb_include module=oxPyxvital template=scor/inc_scor include=true add_row=true}}
  {{/if}}
</table>
