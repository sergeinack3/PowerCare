{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  selectMedecinTraitant = function (input) {
    var form = getForm("traitant-edit");
    if (input.checked) {
      var adresses_par_prat_id = $$("input[name=adresse_par_prat_id]:checked");
      $V(form.medecin_traitant, adresses_par_prat_id.length ? adresses_par_prat_id[0].value : '');
    }
    else {
      $V(form.medecin_traitant, '');
    }

    return onSubmitFormAjax(form, function () {
      reloadCorrespondants('{{$consult->_id}}');
      {{if 'oxPyxvital'|module_active && $app->user_prefs.LogicielFSE == 'oxPyxvital'}}
        if (window.pyxvital_client) {
          window.pyxvital_client.viewFSE('{{$consult->_id}}', 'dPcabinet');
        }
      {{/if}}
    });
  };

  submitAdressePar = function(form) {
    return onSubmitFormAjax(form, function() {
      reloadCorrespondants('{{$consult->_id}}');
      {{if 'oxPyxvital'|module_active && $app->user_prefs.LogicielFSE == 'oxPyxvital'}}
        if (window.pyxvital_client) {
          window.pyxvital_client.viewFSE('{{$consult->_id}}', 'dPcabinet');
        }
      {{/if}}
    });
  };

  Main.add(function () {
    var formTraitant = getForm("editAdresseParPrat");
    var adresseMed = getForm("adresseParMedAutocomplete");
    var urlTraitant = new Url("dPpatients", "httpreq_do_medecins_autocomplete");
    {{if $patient && $patient->function_id}}
    urlTraitant.addParam("function_id", '{{$patient->function_id}}');
    {{/if}}
    urlTraitant.autoComplete(formTraitant._view, formTraitant._view.id + '_autocomplete', {
      minChars:      3,
      updateElement: function (element) {

        var id = element.id.split('-')[1];
        $V(adresseMed.adresse_par_prat_id, id);
        adresseMed.onsubmit();

        addOtherCorrespondant(id);
      }
    });
  });
</script>

<form name="traitant-edit" action="?" method="post">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="dosql" value="do_patients_aed" />
  <input type="hidden" name="patient_id" value="{{$patient->_id}}" />
  <input type="hidden" name="medecin_traitant" value=""/>
</form>

<form name="adresseParMedAutocomplete" method="post" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  {{mb_key object=$consult}}
  <input type="hidden" name="adresse_par_prat_id" value="" />
</form>

{{assign var=medecin value=$patient->_ref_medecin_traitant}}

<form name="editAdresseParPrat" method="post" onsubmit="return submitAdressePar(this);">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  {{mb_key object=$consult}}

  <fieldset class="me-padding-bottom-0 me-small me-fieldset-patient-adresse me-ws-nowrap">
    <legend>
      <label>
        {{mb_field object=$consult field=adresse typeEnum=checkbox
        onchange="togglePatientAddresse(this)"}}
        {{tr}}CConsultation-adresse{{/tr}}
      </label>
    </legend>

    <label for="medecin" class="" title="Recherche du médecin">Médecin</label>
    <input type="text" name="_view" size="30" value=""
           ondblclick="Medecin.edit(this.form, $V(this.form._view), '{{$patient->function_id}}')" class="autocomplete me-small" />
    <div id="traitant-edit-{{$patient->_id}}__view_autocomplete" style="display: none; width: 300px;" class="autocomplete"></div>

    {{if $patient->medecin_traitant_declare === "0"}}
      <label>({{tr}}CPatient-No doctor{{/tr}})</label>
    {{/if}}
    <br />

    {{assign var=medecin_found value=false}}

    {{if $medecin->_id}}
      <label onmouseover="ObjectTooltip.createEx(this, '{{$medecin->_guid}}');" style="border-bottom: none;">
        <input type="radio" name="adresse_par_prat_id" value="{{$medecin->_id}}" class="adresse_par"
               {{if !$consult->adresse}}style="visibility:hidden"{{/if}}
          {{if $consult->adresse_par_prat_id == $medecin->_id}}
            {{assign var=medecin_found value=true}}
            checked
          {{/if}}
               onclick="this.form.onsubmit()" />
        <strong>{{$medecin}}</strong>
      </label>

      {{if $medecin_found}}
        <div>
            {{mb_include module=patients template=inc_choose_medecin_exercice_place
              medecin=$medecin
              object=$consult
              field=adresse_par_exercice_place_id}}
          </div>
      {{/if}}

      <br />
    {{/if}}

    {{assign var=check_corresp value=false}}
    {{foreach from=$patient->_ref_medecins_correspondants item=curr_corresp}}
      {{assign var=medecin value=$curr_corresp->_ref_medecin}}
      <label onmouseover="ObjectTooltip.createEx(this, '{{$medecin->_guid}}');" style="border-bottom: none;">
        <input type="radio" name="adresse_par_prat_id" value="{{$medecin->_id}}" class="adresse_par"
               {{if !$consult->adresse}}style="visibility:hidden"{{/if}}
          {{if !$medecin_found && $consult->adresse_par_prat_id == $medecin->_id}}
            {{assign var=medecin_found value=true}}
            {{assign var=check_corresp value=true}}
            checked
          {{/if}}
               onclick="this.form.onsubmit()" />
        {{$medecin}}
      </label>

      {{if $check_corresp && ($consult->adresse_par_prat_id == $medecin->_id)}}
        <div>
            {{mb_include module=patients template=inc_choose_medecin_exercice_place
              medecin=$medecin
              object=$consult
              field=adresse_par_exercice_place_id}}
        </div>
      {{/if}}

      <br />
    {{/foreach}}

    {{assign var=check_corresp value=false}}
    <div class="adresse_par" {{if !$consult->adresse}}style="visibility:hidden;"{{/if}}>
      <input type="radio" name="adresse_par_prat_id" value="{{if !$medecin_found}}{{$consult->adresse_par_prat_id}}{{/if}}" class="adresse_par"
             {{if !$medecin_found && $consult->adresse_par_prat_id}}
               {{assign var=check_corresp value=true}}
               checked
             {{/if}}
             onclick="Medecin.edit(this.form)" />
      <button id="inc_list_patient_medecins_button_other" type="button" class="search me-tertiary" onclick="$(this).previous('input').checked=true;Medecin.edit(this.form)">{{tr}}Other{{/tr}}</button>
      <span id="inc_list_patient_medecins_span">
        {{if !$medecin_found && $consult->adresse_par_prat_id}}
          {{$consult->_ref_adresse_par_prat}}
        {{/if}}
      </span>
      <button type="button" class="add notext" onclick="addOtherCorrespondant($V(this.previous('input')))"
              {{if $medecin_found || !$consult->adresse_par_prat_id}}style="display: none"{{/if}}>
      </button>

      <div style="float: right; display: inline-block; line-height: 24px;">
        <label>
          <input type="checkbox" name="check_medecin_traitant" value="" onchange="selectMedecinTraitant(this);"
                 {{if $patient->medecin_traitant && ($consult->adresse_par_prat_id == $patient->medecin_traitant)}}checked{{/if}}/>
          {{tr}}CPatient-medecin_traitant{{/tr}}
        </label>
      </div>

      {{if $check_corresp}}
        <div>
            {{mb_include module=patients template=inc_choose_medecin_exercice_place
              medecin=$consult->_ref_adresse_par_prat
              object=$consult
              field=adresse_par_exercice_place_id}}
        </div>
      {{/if}}
    </div>
  </fieldset>
</form>
