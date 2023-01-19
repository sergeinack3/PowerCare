{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=function_id       value=$function_id|default:''}}
{{assign var=function_distinct value=$conf.dPpatients.CPatient.function_distinct}}

{{if !$patient->_id}}
  <div class="small-info">
    {{tr}}CPatient-msg-Please create the patient record to add family link|pl{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<script>
  Main.add(function () {
    Patient.showFamilyLinkWithPatient('{{$patient_family_link->parent_id_1}}', '{{$patient_family_link->parent_id_2}}', '{{$patient->_id}}');

    {{if $patient_family_link->parent_id_1}}
    $('copy_coord_patient_1').show();
    {{/if}}

    {{if $patient_family_link->parent_id_2}}
    $('copy_coord_patient_2').show();
    {{/if}}
  });
</script>

{{*Lien familiaux*}}
<table class="main form me-no-box-shadow me-margin-0 me-padding-0">
  <tr class="me-row-valign">
    <td style="width: 50%;">
      <form name="FrmPatientFamily" method="post" onsubmit="return onSubmitFormAjax(this);">
        <input type="hidden" name="parent_id_1" value="{{$patient_family_link->parent_id_1}}" onchange="this.form.onsubmit();" />
        <input type="hidden" name="parent_id_2" value="{{$patient_family_link->parent_id_2}}" onchange="this.form.onsubmit();" />
        <input type="hidden" name="patient_id" value="{{$patient->_id}}" />
        <input type="hidden" name="callback" value="Patient.callbackFamilyLink" />
        {{mb_key   object=$patient_family_link}}
        {{mb_class object=$patient_family_link}}

        <table class="form me-margin-0 me-no-box-shadow">
          <tr>
            <th class="category me-text-align-left" colspan="3">
              {{tr}}CPatient-Parent|pl{{/tr}}
            </th>
          </tr>
          <tr id="family_link">
            {{me_form_field nb_cells=2 label="CPatient-Parent 1"}}
              <input type="text" name="_seek_parent_id_1" style="width: 13em;" value="{{$patient_family_link->_ref_parent1->_view}}"
                     placeholder="{{tr}}fast-search{{/tr}} {{tr}}CPatient-Parent 1{{/tr}}" "autocomplete"/>
              <button class="cancel notext me-tertiary me-dark"
                      onclick="$V(this.form.parent_id_1, ''); $V(this.form._seek_parent_id_1, ''); $('copy_coord_patient_1').hide();"></button>
              <button type="button" id="copy_coord_patient_1" style="float: right; display: none;"
                      onclick="Patient.getCoordinatesParent($V(this.form.parent_id_1));" class="copy me-notext me-tertiary">
                {{tr}}CPatient-action-Copy contact details of parent 1{{/tr}}
              </button>
              <script>
                Main.add(function () {
                  var form = getForm('FrmPatientFamily');
                  var url = new Url("system", "ajax_seek_autocomplete");
                  url.addParam("object_class", "CPatient");
                  url.addParam("field", "patient_id");
                  url.addParam("function_id", {{$function_id}});
                  url.addParam("view_field", "_patient_view");
                  url.addParam("input_field", "_seek_parent_id_1");
                  {{if $function_distinct && !$app->_ref_user->isAdmin()}}
                    {{if $function_distinct == 1}}
                  url.addParam("where[function_id]", "{{$app->_ref_user->function_id}}");
                    {{else}}
                  url.addParam("where[group_id]", "{{$g}}");
                    {{/if}}
                  {{/if}}
                  url.autoComplete(form.elements._seek_parent_id_1, null, {
                    minChars:           3,
                    method:             "get",
                    select:             "view",
                    dropdown:           false,
                    width:              "300px",
                    afterUpdateElement: function (field, selected) {
                      var view = selected.down('.view');
                      $V(form.parent_id_1, selected.get("guid").split("-")[1]);
                      $V(form._seek_parent_id_1, view.innerHTML);
                      $('copy_coord_patient_1').show();
                    }
                  });
                });
              </script>
            {{/me_form_field}}
          </tr>
          <tr>
            {{me_form_field nb_cells=3 label="CPatient-Parent 2"}}
              <input type="text" name="_seek_parent_id_2" style="width: 13em;" value="{{$patient_family_link->_ref_parent2->_view}}"
                     placeholder="{{tr}}fast-search{{/tr}} {{tr}}CPatient-Parent 2{{/tr}}" "autocomplete"/>
              <button class="cancel notext me-tertiary me-dark"
                      onclick="$V(this.form.parent_id_2, ''); $V(this.form._seek_parent_id_2, ''); $('copy_coord_patient_2').hide();"></button>
              <button type="button" id="copy_coord_patient_2" style="float: right; display: none;"
                      onclick="Patient.getCoordinatesParent($V(this.form.parent_id_2));" class="me-tertiary me-notext copy">
                 {{tr}}CPatient-action-Copy contact details of parent 2{{/tr}}
              </button>
              <script>
                Main.add(function () {
                  var form = getForm('FrmPatientFamily');
                  let url = new Url('system', 'ajax_seek_autocomplete');
                    url.addParam('object_class', 'CPatient');
                    url.addParam('field', 'patient_id');
                    url.addParam('view_field', '_patient_view');
                    url.addParam('input_field', '_seek_parent_id_2');
                    {{if $function_distinct && !$app->_ref_user->isAdmin()}}
                      {{if $function_distinct == 1}}
                        url.addParam("where[function_id]", "{{$app->_ref_user->function_id}}");
                      {{else}}
                        url.addParam("where[group_id]", "{{$g}}");
                      {{/if}}
                    {{/if}}
                  url.autoComplete(form.elements._seek_parent_id_2, null, {
                      minChars:           3,
                      method:             'get',
                      select:             'view',
                      dropdown:           false,
                      width:              '300px',
                      afterUpdateElement: function (field, selected) {
                        var view = selected.down('.view');
                        $V(form.parent_id_2, selected.get("guid").split("-")[1]);
                        $V(form._seek_parent_id_2, view.innerHTML);
                        $('copy_coord_patient_2').show();
                      }
                    });
                });
              </script>
            {{/me_form_field}}
          </tr>

          <tr id="family_link_type">
            {{me_form_field nb_cells=3 label="Type" layout=true}}
              {{mb_field object=$patient_family_link field=type typeEnum=radio onchange="this.form.onsubmit();"}}
            {{/me_form_field}}
          </tr>
        </table>
      </form>
    </td>
    <td style="width: 50%;">
      <table class="form me-margin-0 me-no-box-shadow">
        <tr>
          <th class="category me-valign-top me-text-align-left me-h25" colspan="3" >
            {{tr}}CPatient-Brother and sister(|pl){{/tr}}
          </th>
        </tr>
        <tr>
          <td class="me-valign-top" id="show_family"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
