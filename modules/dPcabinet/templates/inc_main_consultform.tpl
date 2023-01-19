{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dPmedicament"|module_active}}
  {{mb_script module=medicament script=medicament_selector ajax=1}}
{{/if}}

{{mb_script module=patients script=patient      ajax=1}}
{{mb_script module=cabinet  script=exam_dialog  ajax=1}}
{{mb_script module=cabinet  script=consultation ajax=1}}

{{mb_default var=area_focus value=""}}
<script>
  Main.add(function() {
     // UpdateFields de l'autocomplete des traitements
    updateFieldTraitement = function(selected) {
      var oForm = getForm('editFrmExamtraitement');
      $V(oForm.traitement, $V(oForm.traitement)+selected.down(".ucd-view").getText().stripTags().strip()+'\n');
      $V(oForm.produit, "");
    };

    // Autocomplete des medicaments
    var oForm = getForm('editFrmExamtraitement');
    if (oForm && oForm.produit) {
      var urlAuto = new Url("medicament", "httpreq_do_medicament_autocomplete");
      urlAuto.autoComplete(oForm.produit, "_traitement_auto_complete", {
        minChars: 3,
        width: '500px',
        updateElement: updateFieldTraitement,
        callback: function(input, queryString){
          return (queryString + "&produit_max=40");
        }
      } );
    }
  });

  callbackExam = function(consult_id, consult) {
    if (window.tabsConsult) {
      var count_tab = 0;
      var fields = ["motif", "rques", "examen", "histoire_maladie", "projet_soins", "conclusion", "resultats"];
      fields.each(function(field) {
        if (consult[field]) {
          count_tab++
        }
      });
      count_tab += $("examDialog-"+consult_id).select("li:not(.empty)").length;
      Control.Tabs.setTabCount("Examens", count_tab);
    }
  };

  showConsults = function() {
    var url = new Url("cabinet", "ajax_conclusions_consults");
    url.addParam("sejour_id", "{{$consult->sejour_id}}");
    url.addParam("consult_id", "{{$consult->_id}}");
    url.popup(800, 500);
  };

  {{if "dPImeds"|module_active}}
    showLaboResult = function() {
      var url = new Url("dPImeds", "httpreq_vw_sejour_results");
      url.addParam("sejour_id", "{{$consult->sejour_id}}");
      url.popup(800, 700);
    };
  {{/if}}
</script>

{{mb_default var=readonly value=0}}
{{mb_default var=show_header value=0}}
{{mb_default var=isPrescriptionInstalled value="dPprescription"|module_active}}
<table class="form me-no-box-shadow me-margin-top-0">
  <tr>
    <td class="me-padding-0">
      <button class="print me-tertiary me-dark" style="float:right" onclick="Consultation.printExamen('{{$consult->_id}}')">
        {{tr}}CConsultation-action-examen-sheet{{/tr}}
      </button>
    </td>
  </tr>

</table>

{{if $show_header}}
  {{assign var=patient value=$consult->_ref_patient}}
  {{assign var=sejour value=$consult->_ref_sejour}}
  <table class="tbl">
    <tr>
      <th class="title" colspan="2">
        <a style="float: left" href="?m=patients&tab=vw_full_patients&patient_id={{$patient->_id}}">
          {{mb_include module=patients template=inc_vw_photo_identite size=42}}
        </a>

        <h2 style="color: #fff; font-weight: bold;">
          {{$patient}}
          {{if isset($sejour|smarty:nodefaults)}}
            <span style="font-size: 0.7em;"> - {{$sejour->_shortview|replace:"Du":"Séjour du"}}</span>
          {{/if}}
        </h2>
      </th>
    </tr>
  </table>
{{/if}}

<table class="form">
  <tr>
    <td>
      <table class="main layout">
        <tr>
          <td style="width: 50%;">
            <div id="examDialog-{{$consult->_id}}"></div>
            <script>
              {{if !$readonly}}
                ExamDialog.register('{{$consult->_id}}');
              {{/if}}

              onExamComplete = function() {
                FormObserver.changes = 0;
              }
            </script>
          </td>

          {{if "forms"|module_active}}
            <td>
              {{unique_id var=unique_id_exam_forms}}

              <script>
                Main.add(function(){
                  ExObject.loadExObjects("{{$consult->_class}}", "{{$consult->_id}}", "{{$unique_id_exam_forms}}", 0.5);
                });
              </script>

              <fieldset id="list-ex_objects">
                <legend>{{tr}}CExClass|pl{{/tr}}</legend>
                <div id="{{$unique_id_exam_forms}}"></div>
              </fieldset>
            </td>
          {{/if}}
        </tr>
      </table>

      {{assign var=exam_fields value=$consult->getExamFields()}}
      {{assign var=exam_count value=$consult->_exam_fields|@count}}
      {{math assign=text_rows equation="12/round(c/2)" c=$exam_count}}
      <table class="layout main">
        {{foreach name=exam_fields from=$consult->_exam_fields key=current item=field}}
        {{assign var=last value=$smarty.foreach.exam_fields.last}}

        {{if !$last && $current mod 2 == 0}}
        <tr>
          <td class="halfPane">
        {{elseif $current mod 2 == 1}}
          <td class="halfPane">
        {{else}}
        <tr>
          <td colspan="2">
        {{/if}}
          {{* Beginning *}}

          <form name="editFrmExam{{$field}}" method="post" class="editFrmExam" onsubmit="return onSubmitFormAjax(this, onExamComplete);">
            <input type="hidden" name="m" value="cabinet" />
            <input type="hidden" name="dosql" value="do_consultation_aed" />
            <input type="hidden" name="del" value="0" />
            <input type="hidden" name="callback" value="callbackExam" />
            {{mb_key object=$consult}}

            <fieldset class="me-no-box-shadow">
              <legend>
                {{mb_label object=$consult field=$field}}
                {{if $field === "traitement" && $isPrescriptionInstalled && !$readonly}}
                  <input type="text" name="produit" value="" size="12" class="autocomplete" style="margin-top: -3px; margin-bottom: -2px;" />
                  <div style="display:none; width: 350px;" class="autocomplete" id="_traitement_auto_complete"></div>
                {{/if}}
                {{if $field == "conclusion" && $consult->sejour_id}}
                  <button type="button" class="search notext me-tertiary" onclick="showConsults();" title="{{tr}}CConsultation-_list_conclusions{{/tr}}"></button>
                {{/if}}

                {{if $field === "histoire_maladie"}}
                  <button type="button" class="search me-tertiary"
                          onclick="Patient.editModal('{{$consult->patient_id}}', null, null, null, 'bmr_bhre');">{{tr}}CBMRBHRe{{/tr}}</button>
                {{/if}}

                {{if $field === "resultats" && "dPImeds"|module_active}}
                  <button type="button" class="search me-tertiary" onclick="showLaboResult();" >{{tr}}Labo{{/tr}}</button>
                {{/if}}

              </legend>
              {{if $readonly}}
                {{mb_value object=$consult field=$field}}
              {{else}}
                {{mb_field object=$consult field=$field rows=$text_rows onchange="this.form.onsubmit();" form="editFrmExam`$field`"
                           aidesaisie="validateOnBlur: 0"}}
              {{/if}}
            </fieldset>
          </form>
        {{* End *}}
        {{if !$last && $current mod 2 == 0}}
          </td>
        {{elseif $current mod 2 == 1}}
          </td>
        </tr>
        {{else}}
          </td>
        </tr>
        {{/if}}
        {{/foreach}}
      </table>
      {{if !$consult->_refs_dossiers_anesth|@count}}
        <table class="layout main">
          <tr>
            <td class="halfPane"></td>
            <td class="halfPane">
              {{if $consult->_refs_info_checklist|@count}}
                {{mb_include module=cabinet template=inc_select_info_checklist consult_ref=$consult}}
              {{/if}}
            </td>
          </tr>
        </table>
      {{/if}}
    </td>
  </tr>
</table>

{{if $area_focus}}
  <script>
    Main.add(function() {
      // On tente le focus dans la zone de texte désirée
      var area_focus = '{{$area_focus}}';
      var form = getForm('editFrmExam' + area_focus);
      if (form) {
        var field = form.elements[area_focus];
        setTimeout(function() {
          var value = $V(field);
          $V(field.tryFocus(), '', false);
          $V(field, value);
        }, 350);
      }
    });
  </script>
{{/if}}
