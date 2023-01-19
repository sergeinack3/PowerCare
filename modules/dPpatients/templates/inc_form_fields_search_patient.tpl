{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=sexe_disabled value=0}}
{{mb_script module=dPpatients script=patient ajax=$ajax}}

{{mb_default var=nom_jeune_fille value=""}}

{{if !$board}}
    <tr>
        <th class="title" colspan="4">{{tr}}CPatient-search-folder-patient-title{{/tr}}</th>
    </tr>
{{/if}}
<script>
    Main.add(function () {
        Patient.togglePraticien();

        var form = getForm(Patient.form_search);

        [
            form.nom,
            form.prenom,
            form.cp,
            form.ville,
            form.Date_Day,
            form.Date_Month,
            form.Date_Year
        ].invoke("observe", "keydown", Patient.togglePraticien);

        {{if ($cp || $ville || ($conf.dPpatients.CPatient.tag_ipp && $patient_ipp) || $prat_id || $sexe || ($conf.dPplanningOp.CSejour.tag_dossier && $patient_nda)) && !$see_link_prat}}
        Patient.toggleSearch();
        {{/if}}
    });
</script>
<tr>
    {{me_form_field nb_cells=2 mb_class="CPatient" label='CPatient-_p_last_name' mb_field="nom" }}
        <input tabindex="1" type="text" name="nom" value="{{$nom|stripslashes}}"/>
    {{/me_form_field}}
    <input type="hidden" name="nom_jeune_fille" value="{{$nom_jeune_fille|stripslashes}}"/>

    {{me_form_field style_css="display: none;" nb_cells=2 class="field_advanced halfPane" mb_class="CPatient" mb_field="cp"}}
        <input tabindex="6" type="text" name="cp" value="{{$cp|stripslashes}}"/>
    {{/me_form_field}}
</tr>

<tr>
    {{me_form_field nb_cells=2 mb_class="CPatient" label='CPatient-_p_first_name' mb_field="prenom"}}
        <input tabindex="2" type="text" name="prenom" value="{{$prenom|stripslashes}}"/>
    {{/me_form_field}}

    {{me_form_field style_css="display: none;" nb_cells=2 class="field_advanced" mb_class="CPatient" mb_field="ville" }}
        <input tabindex="7" type="text" name="ville" value="{{$ville|stripslashes}}"/>
    {{/me_form_field}}
</tr>

<tr>
    {{me_form_field layout=true field_class="me-padding-0 me-no-border" nb_cells=2 label="CPatient-naissance"}}
    {{mb_include module=patients template=inc_select_date date=$naissance tabindex=3}}
    {{/me_form_field}}

    {{me_form_field style_css="display: none;" nb_cells=2 class="field_advanced" mb_class="CPatient" mb_field="_IPP" }}
        <input tabindex="8" type="text" name="patient_ipp" value="{{$patient_ipp}}"/>
    {{/me_form_field}}
</tr>

<tr>
    {{me_form_field nb_cells=2  mb_class="CPatient" mb_field="sexe"
    style_css="display: none;vertical-align: middle;" nb_cells=2 class="field_advanced"}}
        <select name="sexe" {{if $sexe_disabled}}disabled{{/if}}>
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            <option value="m" {{if $sexe == "m"}}selected{{/if}}>
                {{tr}}CPatient.sexe.m{{/tr}}
            </option>
            <option value="f" {{if $sexe == "f"}}selected{{/if}}>
                {{tr}}CPatient.sexe.f{{/tr}}
            </option>
        </select>
    {{/me_form_field}}


    {{me_form_field label="CMediusers-praticien" nb_cells=2 class="field_advanced" style_css="display: none;"}}
        <div class="small-info" id="prat_id_message">
            {{tr}}CMediusers-msg-select-patient{{/tr}}
        </div>
        <select name="prat_id" tabindex="5" style="width: 13em; display: none;">
            <option value="">&mdash; {{tr}}CMediusers-select-praticien{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_mediuser list=$prats selected=$prat_id}}
        </select>
    {{/me_form_field}}
</tr>

<tr>
    {{me_form_field style_css="display: none;" nb_cells=2 class="field_advanced" label="CPatient-NDA" title_label="CPatient-NDA"}}
        <input tabindex="8" type="text" name="patient_nda" value="{{$patient_nda}}"/>
    {{/me_form_field}}

    {{me_form_field style_css="display: none;" nb_cells=2 class="field_advanced" label="CPatient-_matricule-ins"
    mb_class="CPatient" mb_field="matricule" null_chars="_| "}}
      {{mb_field class=CPatient field="matricule" onblur="Patient.checkMatricule(this);"}}
    {{/me_form_field}}
</tr>
