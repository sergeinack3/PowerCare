{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=medecin ajax=1}}

<script>
    Medecin.set = function (id, view) {
        if (this.form.medecin_traitant) {
            $V(this.form.medecin_traitant, id);
        } else if (this.form.medecin_id) {
            $V(this.form.medecin_id, id);
        } else {
            $V(this.form.pharmacie_id, id);
        }
        $V(this.form._view, view);
    };

    submitMedecin = function (form) {
        if (!$V(form.del) && ($V(form.dosql) != "do_patients_aed") && $V(form.medecin_id)) {
            Control.Tabs.setTabCount("medecins", "+1");
        }
        // Update main form for unexisting patient
        if (!$V(form.patient_id)) {
            $V(document.editFrm.medecin_traitant, $V(form.medecin_traitant));
            return;
        }

        // Submit for existing patient
        return onSubmitFormAjax(form, {
            onComplete: function () {
                $('{{$widget_id}}').widget.refresh()
            }
        });
    };

    updateMedTraitant = function (form, type) {
        if ((type == 'medecin' && $V(form.medecin_traitant)) ||
            (type == 'pharmacie' && $V(form.pharmacie_id))) {
            Control.Tabs.setTabCount('medecins', '+1');
        }
        return submitMedecin(form);
    };

    linkMediuserToMedecin = function (user_id) {
        var url = new Url('patients', 'ajax_link_user_to_medecin');
        url.addParam('user_id', user_id);
        url.addParam('patient_id', '{{$patient->_id}}');
        url.requestModal();
    };

    Main.add(function () {
        var formTraitant = getForm("traitant-edit-{{$patient->_id}}");
        var urlTraitant = new Url("dPpatients", "httpreq_do_medecins_autocomplete");
        {{if $patient && $patient->function_id}}
        urlTraitant.addParam("function_id", '{{$patient->function_id}}');
        {{/if}}
        urlTraitant.autoComplete(formTraitant._view, formTraitant._view.id + '_autocomplete', {
            minChars: 0,
            updateElement: function (element) {
                $V(formTraitant.medecin_traitant, element.id.split('-')[1]);
                $V(formTraitant._view, element.select(".view")[0].innerHTML.stripTags());
                $V(getForm('editFrm').medecin_traitant_declare, '1');
            }
        });

        {{if $patient && $patient->_id}}
        var formPharmacie = getForm("pharmacie-{{$patient->_id}}");
        var urlPharmacie = new Url("dPpatients", "httpreq_do_medecins_autocomplete");
        {{if $patient && $patient->function_id}}
        urlPharmacie.addParam("function_id", '{{$patient->function_id}}');
        {{/if}}
        urlPharmacie.addParam("type", 'pharmacie');
        urlPharmacie.autoComplete(formPharmacie._view, formPharmacie._view.id + '_autocomplete', {
            minChars: 3,
            updateElement: function (element) {
                $V(formPharmacie.pharmacie_id, element.id.split('-')[1]);
                $V(formPharmacie._view, element.select(".view")[0].innerHTML.stripTags());
            }
        });

        var formCorresp = getForm("correspondant-new-{{$patient->_id}}");
        var urlCorresp = new Url("dPpatients", "httpreq_do_medecins_autocomplete");
        urlCorresp.autoComplete(formCorresp._view, formCorresp._view.id + '_autocomplete', {
            minChars: 3,
            updateElement: function (element) {
                $V(formCorresp.medecin_id, element.id.split('-')[1]);
                $V(formCorresp._view, element.select(".view")[0].innerHTML.stripTags());
            }
        });
        {{/if}}

    });
</script>

<table class="form me-no-box-shadow">
    <tr>
        <th style="width: 30%;">
            {{mb_label object=$patient field="medecin_traitant"}}
            {{mb_field object=$patient field="medecin_traitant" hidden=1}}
        </th>
        <td>
            {{mb_ternary var=medecin_traitant_id   test=$patient->_id value=$patient->medecin_traitant other=""}}

            {{* mb_ternary won't work : will throw a warning *}}
            {{assign var=medecin_traitant_view value=""}}
            {{if $patient->_id}}
                {{assign var=medecin_traitant_view value=$patient->_ref_medecin_traitant->_shortview}}
            {{/if}}

            <form name="traitant-edit-{{$patient->_id}}" method="post" onsubmit="return false">
                <input type="hidden" name="m" value="{{$m}}"/>
                <input type="hidden" name="dosql" value="do_patients_aed"/>
                <input type="hidden" name="patient_id" value="{{$patient->_id}}"/>
                <input type="hidden" name="medecin_traitant" value="{{$medecin_traitant_id}}"
                       onchange="updateMedTraitant(this.form, 'medecin');"/>
                {{mb_field object=$patient field=medecin_traitant_declare hidden=true}}

        <div>
          <input type="text" name="_view" size="50" value="{{$medecin_traitant_view|smarty:nodefaults}}"
                 {{if $patient->medecin_traitant_declare === "0"}}readonly{{/if}}
                 ondblclick="var button = this.next('button.search'); if (button.disabled) { return; } button.onclick();"
                 class="autocomplete"
          />
          <div id="traitant-edit-{{$patient->_id}}__view_autocomplete" style="display: none; width: 300px;" class="autocomplete"></div>
          <button class="search me-tertiary" type="button" onclick="Medecin.edit(this.form, $V(this.form._view), '{{$patient->function_id}}')"
                  {{if $patient->medecin_traitant_declare === "0"}}disabled{{/if}}>{{tr}}Choose{{/tr}}</button>
          {{if $user->_is_medecin}}
            <button class="fa fa-user-md me-tertiary" type="button"
                    onclick="linkMediuserToMedecin('{{$user->_id}}');">{{tr}}CPatient.user_is_medecin_traitant{{/tr}}</button>{{/if}}
          <button class="cancel notext me-tertiary me-dark" type="button" onclick="Medecin.del(this.form); $V(getForm('editFrm').medecin_traitant_declare, '');">{{tr}}Delete{{/tr}}</button>

          <label>
            <input type="checkbox" name="_no_medecin_traitant"
                   {{if $patient->_id && $patient->medecin_traitant_declare === "0"}}checked{{/if}}
                    data-patient-id="{{$patient->_id}}"
                   onclick="Medecin.toggleMedTraitant(this);" /> {{tr}}CPatient-No medecin traitant{{/tr}}
          </label>
        </div>

        {{if $patient->_ref_medecin_traitant->_ref_exercice_places|@count}}
            <div>
              {{mb_include module=patients template=inc_choose_medecin_exercice_place
                medecin=$patient->_ref_medecin_traitant
                object=$patient
                field=medecin_traitant_exercice_place_id}}
            </div>
        {{/if}}
      </form>
    </td>
  </tr>

  {{if $patient && $patient->_id}}
    <tr>
      <th>{{mb_label object=$patient field=pharmacie_id}}</th>
      <td>
        {{mb_ternary var=pharmacie_id   test=$patient->_id value=$patient->pharmacie_id other=""}}
        {{assign var=pharmacie_view value=""}}
        {{if $patient->_id}}
          {{assign var=pharmacie_view value=$patient->_ref_pharmacie->_shortview}}
        {{/if}}
        <form name="pharmacie-{{$patient->_id}}" action="?" method="post" onsubmit="return submitMedecin(this)">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="dosql" value="do_patients_aed" />
          <input type="hidden" name="patient_id" value="{{$patient->_id}}" />
          <input type="hidden" name="pharmacie_id" value="{{$pharmacie_id}}" onchange="updateMedTraitant(this.form, 'pharmacie');" />
          <input type="text" name="_view" size="50" value="{{$pharmacie_view|smarty:nodefaults}}"
                 ondblclick="Medecin.edit(this.form, $V(this.form._view), '{{$patient->function_id}}', 'pharmacie')"
                 class="autocomplete" />
          <div id="pharmacie-edit-{{$patient->_id}}__view_autocomplete" style="display: none; width: 300px;"
               class="autocomplete"></div>
          <button class="search me-tertiary" type="button"
                  onclick="Medecin.edit(this.form, $V(this.form._view), '{{$patient->function_id}}', 'pharmacie')">{{tr}}Choose{{/tr}}</button>
          <button class="cancel notext me-tertiary me-dark" type="button" onclick="Medecin.delPharmacie(this.form)">{{tr}}Delete{{/tr}}</button>
        </form>
      </td>
    </tr>
    {{foreach from=$patient->_ref_medecins_correspondants item=curr_corresp name=corresp}}
      <tr>
        {{if $smarty.foreach.corresp.first}}
          <th rowspan="{{$patient->_ref_medecins_correspondants|@count}}">{{tr}}CPatient-back-medecins_correspondants{{/tr}}</th>
        {{/if}}
        <td>
          {{if $curr_corresp->_ref_medecin->_ref_spec_cpam->_id}}
            <span>{{$curr_corresp->_ref_medecin->_ref_spec_cpam->text}}</span>
            <br>
          {{/if}}
          <form name="correspondant-edit-{{$curr_corresp->_id}}" method="post" onsubmit="return false;">
            <input type="hidden" name="m" value="{{$m}}" />
            <input type="hidden" name="dosql" value="do_correspondant_aed" />
            <input type="hidden" name="del" value="" onchange="submitMedecin(this.form)" />
            <input type="hidden" name="correspondant_id" value="{{$curr_corresp->_id}}" />
            <input type="hidden" name="patient_id" value="{{$curr_corresp->_ref_patient->_id}}" />
            <input type="hidden" name="medecin_id" value="{{$curr_corresp->_ref_medecin->_id}}" onchange="submitMedecin(this.form)" />

            <div>
              <input type="text" name="_view" size="50" value="{{$curr_corresp->_ref_medecin->_shortview}}"
                     ondblclick="Medecin.edit(this.form, $V(this.form._view), '{{$patient->function_id}}')" readonly="readonly" />
              <button class="search me-tertiary" type="button"
                      onclick="Medecin.edit(this.form, $V(this.form._view), '{{$patient->function_id}}')">{{tr}}Change{{/tr}}</button>
              <button class="cancel notext me-tertiary me-dark" type="button" onclick="Medecin.del(this.form)">{{tr}}Delete{{/tr}}</button>
            </div>

            <div>
                {{mb_include module=patients template=inc_choose_medecin_exercice_place
                  medecin=$curr_corresp->_ref_medecin
                  object=$curr_corresp
                  field=medecin_exercice_place_id}}
            </div>
          </form>
        </td>
      </tr>
    {{/foreach}}
    <tr>
      <th>{{tr}}CCorrespondant-title-create{{/tr}}</th>
      <td>
        <form name="correspondant-new-{{$patient->_id}}" method="post" onsubmit="return submitMedecin(this)">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="dosql" value="do_correspondant_aed" />
          <input type="hidden" name="patient_id" value="{{$patient->_id}}" />
          <input type="hidden" name="medecin_id" value="" onchange="this.form.onsubmit()" />
          <input type="text" name="_view" size="50" value=""
                 ondblclick="Medecin.edit(this.form, $V(this.form._view), '{{$patient->function_id}}')" class="autocomplete" />
          <div id="correspondant-new-{{$patient->_id}}__view_autocomplete" style="display: none; width: 300px;"
               class="autocomplete"></div>
          <button class="search me-tertiary" type="button"
                  onclick="Medecin.edit(this.form, $V(this.form._view), '{{$patient->function_id}}')">{{tr}}Choose{{/tr}}</button>
        </form>
      </td>
    </tr>
  {{else}}
    <tr>
      <td colspan="2" class="text">
        <div class="small-info">
          {{tr}}CPatient-msg-create-fiche-patient-medecin{{/tr}}
        </div>
      </td>
    </tr>
  {{/if}}
</table>
