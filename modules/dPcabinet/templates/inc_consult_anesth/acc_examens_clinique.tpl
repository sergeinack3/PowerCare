{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  callbackExam = function(consult_id, consult) {
    if (!window.tabsConsultAnesth) {
      return;
    }
    var count_tab = 0;
    switch (consult._class) {
      case "CConsultAnesth":
        var fields = ["examenCardio", "examenPulmo", "examenDigest", "examenAutre"];
        fields.each(function(field) {
          if (consult[field]) {
            count_tab++
          }
        });
        if ($V(getForm("editFrmExamenConsult").examen)) {
          count_tab++;
        }
        break;
      case "CConsultation":
        if (consult.examen) {
          count_tab++;
        }
        getForm("editAnesthExamenClinique").select("textarea").each(function(textarea) {
          if ($V(textarea)) {
            count_tab++;
          }
        });
      default:
    }
    count_tab += $("examDialog-" + consult.consultation_id).select("li:not(.empty)").length;
    Control.Tabs.setTabCount("Exams", count_tab);
  }
</script>
<table class="main form me-no-box-shadow me-no-align">
  <tr>
    <td class="me-padding-4">
      <table class="main layout">
        <tr>
          <td style="width: 50%;">
            <!-- Fiches d'examens -->
            {{mb_script module="cabinet" script="exam_dialog" ajax=1}}
            <div id="examDialog-{{$consult->_id}}"></div>
            <script>
              ExamDialog.register('{{$consult->_id}}','{{$consult_anesth->_id}}');
            </script>
          </td>
          
          {{if "forms"|module_active}}
            <td>
              {{unique_id var=unique_id_exam_forms}}
              
              <script>
                Main.add(function(){
                  ExObject.loadExObjects("{{$consult_anesth->_class}}", "{{$consult_anesth->_id}}", "{{$unique_id_exam_forms}}", 0.5);
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
      
      <form name="editAnesthExamenClinique" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
        <input type="hidden" name="m" value="cabinet" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
        <input type="hidden" name="callback" value="callbackExam" />
        {{mb_key object=$consult_anesth}}
        <table class="layout main">
          <tr>
            <td class="halfPane">
              <fieldset class="me-no-box-shadow">
                <legend>{{mb_label object=$consult_anesth field="examenCardio"}}</legend>
                {{mb_field object=$consult_anesth field="examenCardio" rows="4" onchange="this.form.onsubmit()" form="editAnesthExamenClinique"
                  aidesaisie="validateOnBlur: 0"}}
              </fieldset>
            </td>
            <td class="halfPane">
              <fieldset class="me-no-box-shadow">
                <legend>{{mb_label object=$consult_anesth field="examenPulmo"}}</legend>
                {{mb_field object=$consult_anesth field="examenPulmo" rows="4" onchange="this.form.onsubmit()" form="editAnesthExamenClinique"
                  aidesaisie="validateOnBlur: 0"}}
              </fieldset>
            </td>
          </tr>
          <tr>
            <td class="halfPane">
              <fieldset class="me-no-box-shadow">
                <legend>{{mb_label object=$consult_anesth field="examenDigest"}}</legend>
                {{mb_field object=$consult_anesth field="examenDigest" rows="4" onchange="this.form.onsubmit()" form="editAnesthExamenClinique"
                  aidesaisie="validateOnBlur: 0"}}
              </fieldset>
            </td>
            <td class="halfPane">
              <fieldset class="me-no-box-shadow">
                <legend>{{mb_label object=$consult_anesth field="examenAutre"}}</legend>
                {{mb_field object=$consult_anesth field="examenAutre" rows="4" onchange="this.form.onsubmit()" form="editAnesthExamenClinique"
                  aidesaisie="validateOnBlur: 0"}}
              </fieldset>
            </td>
          </tr>
        </table>
      </form>

      <fieldset class="me-no-box-shadow">
        <legend>
          {{mb_label object=$consult field=histoire_maladie}}

          <button type="button" class="search not-printable"
                  onclick="Patient.editModal('{{$consult->patient_id}}', null, null, null, 'bmr_bhre');">{{tr}}CBMRBHRe{{/tr}}</button>
        </legend>
        <form name="editHistoireMaladie" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
          <input type="hidden" name="m" value="cabinet" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
          <input type="hidden" name="callback" value="callbackExam" />
          {{mb_key object=$consult_anesth}}
          {{mb_field object=$consult_anesth field="histoire_maladie" rows="4" onchange="this.form.onsubmit()" form="editHistoireMaladie" typeEnum="checkbox"}}
        </form>
      </fieldset>

      <fieldset class="me-no-box-shadow">
        <legend>{{mb_label object=$consult field="examen"}}</legend>
        <form name="editAnesthPoidsStable" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
          <input type="hidden" name="m" value="cabinet" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
          <input type="hidden" name="callback" value="callbackExam" />
          {{mb_key object=$consult_anesth}}
          {{mb_field object=$consult_anesth field="poids_stable" rows="4" onchange="this.form.onsubmit()" form="editAnesthPoidsStable" typeEnum="checkbox"}}
          {{mb_label object=$consult_anesth field="poids_stable"}}
        </form>
        <form name="editFrmExamenConsult" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
          <input type="hidden" name="m" value="cabinet" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="dosql" value="do_consultation_aed" />
          <input type="hidden" name="callback" value="callbackExam" />
          {{mb_key object=$consult}}
          {{mb_field object=$consult field="examen" rows="4" onchange="this.form.onsubmit()" form="editFrmExamenConsult"
          aidesaisie="validateOnBlur: 0"}}
        </form>
      </fieldset>
    </td>
  </tr>
</table>