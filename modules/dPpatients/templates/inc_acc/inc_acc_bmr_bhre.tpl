{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=bmrbhre ajax=$ajax}}

{{assign var=bmr_bhre value=$patient->_ref_bmr_bhre}}

<form name="editBMRBHRe" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_bmr_bhre_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="patient_id" value="{{$patient->_id}}" />

  {{mb_key object=$bmr_bhre}}

  <input type="hidden" name="callback" value="Patient.fillBMRBHeId" />

  <table class="form me-margin-0 me-no-box-shadow" style="width: 30%">
    <tr>
      <th class="title" colspan="2">
        {{mb_include module=system template=inc_object_history object=$bmr_bhre}}

        Statut BMR - BHRe
      </th>
    </tr>
    <tr>
      {{me_form_field layout=true nb_cells=2 mb_object=$bmr_bhre mb_field="bmr" class="me-padding-top-16"}}
        {{mb_field object=$bmr_bhre field=bmr typeEnum=radio onchange="BMRBHRE.checkDatesEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 field_class="me-form-group-inline" mb_object=$bmr_bhre mb_field="bmr_debut"}}
        {{mb_field object=$bmr_bhre field=bmr_debut form=editBMRBHRe register=1 style="max-width: 120px" onchange="BMRBHRE.checkRadioEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 field_class="me-form-group-inline" mb_object=$bmr_bhre mb_field="bmr_fin"}}
        {{mb_field object=$bmr_bhre field=bmr_fin form=editBMRBHRe register=1 style="max-width: 120px" onchange="BMRBHRE.checkRadioEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field layout=true nb_cells=2 mb_object=$bmr_bhre mb_field="bhre" class="me-padding-top-16"}}
        {{mb_field object=$bmr_bhre field=bhre typeEnum=radio onchange="BMRBHRE.checkDatesEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$bmr_bhre field_class="me-form-group-inline" mb_field="bhre_debut"}}
        {{mb_field object=$bmr_bhre field=bhre_debut form=editBMRBHRe register=1 style="max-width: 120px" onchange="BMRBHRE.checkRadioEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$bmr_bhre field_class="me-form-group-inline" mb_field="bhre_fin"}}
        {{mb_field object=$bmr_bhre field=bhre_fin form=editBMRBHRe register=1 style="max-width: 120px" onchange="BMRBHRE.checkRadioEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field layout=true nb_cells=2 mb_object=$bmr_bhre mb_field="hospi_etranger" class="me-padding-top-16"}}
        {{mb_field object=$bmr_bhre field=hospi_etranger typeEnum=radio onchange="BMRBHRE.checkDatesEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$bmr_bhre field_class="me-form-group-inline" mb_field="hospi_etranger_debut"}}
        {{mb_field object=$bmr_bhre field=hospi_etranger_debut form=editBMRBHRe register=1 style="max-width: 120px" onchange="BMRBHRE.checkRadioEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$bmr_bhre field_class="me-form-group-inline" mb_field="hospi_etranger_fin"}}
        {{mb_field object=$bmr_bhre field=hospi_etranger_fin form=editBMRBHRe register=1 style="max-width: 120px" onchange="BMRBHRE.checkRadioEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field layout=true nb_cells=2 mb_object=$bmr_bhre mb_field="rapatriement_sanitaire" class="me-padding-top-16"}}
        {{mb_field object=$bmr_bhre field=rapatriement_sanitaire typeEnum=radio style="max-width: 120px" onchange="this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field layout=true nb_cells=2 mb_object=$bmr_bhre mb_field="ancien_bhre" class="me-padding-top-16"}}
        {{mb_field object=$bmr_bhre field=ancien_bhre typeEnum=radio style="max-width: 120px" onchange="this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field layout=true nb_cells=2 mb_object=$bmr_bhre mb_field="bhre_contact" class="me-padding-top-16"}}
        {{mb_field object=$bmr_bhre field=bhre_contact typeEnum=radio style="max-width: 120px" onchange="BMRBHRE.checkDatesEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$bmr_bhre field_class="me-form-group-inline" mb_field="bhre_contact_debut"}}
        {{mb_field object=$bmr_bhre field=bhre_contact_debut form=editBMRBHRe register=1 style="max-width: 120px" onchange="BMRBHRE.checkRadioEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$bmr_bhre field_class="me-form-group-inline" mb_field="bhre_contact_fin"}}
        {{mb_field object=$bmr_bhre field=bhre_contact_fin form=editBMRBHRe register=1 style="max-width: 120px" onchange="BMRBHRE.checkRadioEditPatient(this); this.form.onsubmit();"}}
      {{/me_form_field}}
    </tr>
  </table>
</form>
