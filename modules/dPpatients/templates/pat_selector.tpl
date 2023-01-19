{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $app->user_prefs.LogicielLectureVitale == 'vitaleVision'}}
    {{mb_include module=dPpatients template=inc_vitalevision debug=false keepFiles=true}}
{{/if}}

{{assign var=modFSE value="fse"|module_active}}

<script>
    PatientFromSelector = {
        create: function (useVitale) {
            this.edit(0, useVitale);
        },

        edit: function (patient_id, useVitale) {
            var url = new Url("patients", "vw_edit_patients");
            url.addParam("patient_id", patient_id);
            url.addParam("dialog", 1);

            var oForm;
            if (oForm = getForm("patientSearch")) {
                url.addElement(oForm.nom, "name");
                url.addElement(oForm.prenom, "firstName");
                url.addElement(oForm.Date_Day, "naissance_day");
                url.addElement(oForm.Date_Month, "naissance_month");
                url.addElement(oForm.Date_Year, "naissance_year");
                url.addParam("modal", "1");
                url.addParam("callback", "window.parent.PatientFromSelector.createCallback");
            }

            // Ajout du praticien si l'on est en prise de rdv
            var formConsult;
            if (formConsult = getForm("editFrm")) {
                if (formConsult.chir_id) {
                    url.addElement(formConsult.chir_id, "praticien_id");
                }
            }

            if (useVitale || (oForm == document.patientEdit)) {
                url.addParam("useVitale", 1);
            }

            url.modal({width: "95%", height: "95%"});
        },

        select: function (patient, medecin_traitant) {
            PatSelector.set(patient);
            if (typeof Medecin != "undefined" && Medecin.form) {
                Medecin.set(patient.medecin_traitant, medecin_traitant);
            }

            try {
                if (window.parent && window.parent.Transport && window.parent.Transport.fillFields) {
                    // fill other infos
                    Transport.fillFields(getForm('editTransport'), patient.patient_id);
                }
            } catch (e) {
            }

            Control.Modal.close();
        },

        updateFromVitale: function (patient_id, view, sexe) {
            var url = new Url("patients", "ajax_update_patient_from_vitale");
            url.addParam("patient_id", patient_id);
            url.requestUpdate("systemMsg", this.select.curry(patient_id, view, sexe));
        },

        createCallback: function (id, obj) {
            const reopenPatientFile = !!getForm("patientSearch")
            {{if "dPpatients CPatient auto_selected_patient"|gconf}}
            PatientFromSelector.select(obj);
            {{else}}
            var form = getForm("patientSearch");
            $V(form.nom, obj.nom);
            $V(form.prenom, obj.prenom);
            $V(form.useVitale, obj._bind_vitale);

            if (obj.naissance) {
                var split = obj.naissance.split(/-/);

                if (form.Date_Day) {
                    $V(form.Date_Year, split[0]);
                    $V(form.Date_Month, split[1]);
                    $V(form.Date_Day, split[2]);
                } else {
                    $V(form, Date.fromDATE(obj.naissance).toLocaleDate());
                }
            }

            //$V(form.nom, obj.nom);
            form.onsubmit();
            {{/if}}

            if (reopenPatientFile) {
               PatientFromSelector.edit(obj.patient_id)
            }
        }
    };

    Main.add(function () {
        var form = getForm('patientSearch');

        {{if $modFSE && $modFSE->canRead() && $app->user_prefs.LogicielLectureVitale == 'none'}}
        var urlFSE = new Url("dPpatients", "pat_selector");
        urlFSE.addParam("useVitale", 1);
        urlFSE.addParam("dialog", 1);
        urlFSE.updateElement = form.up(".content");
        window.urlFSE = urlFSE;
        {{/if}}

        var callback = function (e) {
            var elt = Event.element(e);

            if (elt.name !== 'start') {
                $V(elt.form.elements.start, '0');
            }
        };

        form.on('change', 'input, select', callback);
        form.on('ui:change', 'input, select', callback);

        {{if $patient->nom || $patient->prenom || $patient->naissance}}
        form.onsubmit();
        {{/if}}

        form.nom.tryFocus();
    });

    changePagePat = function (start) {
        var form = getForm('patientSearch');
        $V(form.elements.start, start);
        form.onsubmit();
    }
</script>

<div id="modal-beneficiaire" style="display:none; text-align:center;">
    <p id="msg-multiple-benef">
        {{tr}}CPatient.card_lot_of_benifits{{/tr}} :
    </p>
    <p id="msg-confirm-benef" style="display: none;"></p>
    <p id="benef-nom">
        <select id="modal-beneficiaire-select"></select>
        <span></span>
    </p>
    <div>
        <button type="button" class="tick me-primary"
                onclick="VitaleVision.search(getForm('patientSearch'), $V($('modal-beneficiaire-select'))); VitaleVision.modalWindow.close();">{{tr}}Choose{{/tr}}</button>
        <button type="button" class="cancel" onclick="VitaleVision.modalWindow.close();">{{tr}}Cancel{{/tr}}</button>
    </div>
</div>

<div class="small-info me-border-width-1">{{tr}}CPatient.search_before_create{{/tr}}</div>

{{if $patVitale}}

    <!-- Formulaire de mise à jour Vitale -->
    <form name="patientEdit" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="patients"/>
        <input type="hidden" name="dosql" value="do_patients_aed"/>
        <input type="hidden" name="_bind_vitale" value="do"/>
        {{mb_field object=$patVitale field="patient_id" hidden="true"}}

        <table class="form me-box-shadow-table">
            <tr>
                <th class="category me-text-align-left" colspan="4">{{tr}}CPatient.values_sesam_vital{{/tr}}</th>
            </tr>

            <tr>
                {{me_form_field nb_cells=2 mb_object=$patVitale mb_field="nom"}}
                    <div class="me-field-content">
                        {{mb_value object=$patVitale field="nom"}}
                        {{mb_field object=$patVitale field="nom" hidden="true"}}
                    </div>
                {{/me_form_field}}

                {{me_form_field nb_cells=4 mb_object=$patVitale mb_field="adresse"}}
                    <div class="me-field-content">
                        {{mb_value object=$patVitale field="adresse"}}
                        {{mb_field object=$patVitale field="adresse" hidden="true"}}
                    </div>
                {{/me_form_field}}
            </tr>

            <tr>
                {{me_form_field nb_cells=4 mb_object=$patVitale mb_field="prenom"}}
                    <div class="me-field-content">
                        {{mb_value object=$patVitale field="prenom"}}
                        {{mb_field object=$patVitale field="prenom" hidden="true"}}
                    </div>
                {{/me_form_field}}
            </tr>

            <tr>
                {{me_form_field nb_cells=2 mb_object=$patVitale mb_field="naissance"}}
                    <div class="me-field-content">
                        {{mb_value object=$patVitale field="naissance"}}
                        {{mb_field object=$patVitale field="naissance" hidden="true"}}
                        {{mb_field object=$patVitale field="rang_naissance" hidden="true"}}
                    </div>
                {{/me_form_field}}

                {{me_form_field nb_cells=2 mb_object=$patVitale mb_field="cp"}}
                    <div class="me-field-content">
                        {{mb_value object=$patVitale field="cp"}}
                        {{mb_field object=$patVitale field="cp" hidden="true"}}
                    </div>
                {{/me_form_field}}
            </tr>

            <tr>
                {{me_form_field nb_cells=2 mb_object=$patVitale mb_field="matricule"}}
                    <div class="me-field-content">
                        {{mb_value object=$patVitale field="matricule"}}
                        {{mb_field object=$patVitale field="matricule" hidden="true"}}
                        {{mb_field object=$patVitale field="assure_matricule" hidden="true"}}
                        {{mb_field object=$patVitale field="rang_beneficiaire" hidden="true"}}
                        {{mb_field object=$patVitale field="qual_beneficiaire" hidden="true"}}
                    </div>
                {{/me_form_field}}

                {{me_form_field nb_cells=2 mb_object=$patVitale mb_field="ville"}}
                    <div class="me-field-content">
                        {{mb_value object=$patVitale field="ville"}}
                        {{mb_field object=$patVitale field="ville" hidden="true"}}
                    </div>
                {{/me_form_field}}
            </tr>

            <tr>
                {{me_form_field nb_cells=4 mb_object=$patVitale mb_field="regime_sante"}}
                    <div class="me-field-content">
                        {{mb_value object=$patVitale field="regime_sante"}}
                        {{mb_field object=$patVitale field="code_regime" hidden="true"}}
                        {{mb_field object=$patVitale field="caisse_gest" hidden="true"}}
                        {{mb_field object=$patVitale field="centre_gest" hidden="true"}}
                        {{mb_field object=$patVitale field="regime_sante" hidden="true"}}
                    </div>
                {{/me_form_field}}
            </tr>
            <tr>
                <td colspan="2" class="button">
                    {{if $can->edit}}
                        <button class="new me-primary" type="button"
                                onclick="PatientFromSelector.create({{$useVitale}});">
                            {{tr}}CPatient.create_with_vitale{{/tr}}
                        </button>
                    {{/if}}
                </td>
                <td colspan="2" class="button">
                    <button class="cancel me-tertiary me-dark" type="button"
                            onclick="Control.Modal.close()">{{tr}}Cancel{{/tr}}</button>
                </td>
            </tr>
        </table>
    </form>
{{/if}}

<!-- Formulaire de recherche -->
<form name="patientSearch" method="get" onsubmit="return Url.update(this, 'patients-list')">
    <input type="hidden" name="m" value="patients"/>
    <input type="hidden" name="a" value="searchPatient"/>
    <input type="hidden" name="useVitale" value=""/>
    <input type="hidden" name="mode" value="selector"/>

    {{if 'dPpatients CPatient search_paging'|gconf}}
        <input type="hidden" name="start" value="{{$start}}"/>
    {{/if}}

    <table class="form me-box-shadow-table">
        <tr>
            <th class="title" colspan="4">{{tr}}common-Selection criteria{{/tr}}</th>
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_class=CPatient mb_field="nom"}}
            {{mb_field object=$patient field=nom prop="str" size=30 tabindex=1}}
            {{/me_form_field}}

            {{me_form_field nb_cells=2 mb_class=CPatient mb_field="naissance"}}
            {{mb_include module=patients template=inc_select_date date=$patient->naissance tabindex=3}}
            {{/me_form_field}}
        </tr>

        <tr>
            {{me_form_field nb_cells=2 mb_class=CPatient mb_field="prenom"}}
            {{mb_field object=$patient field=prenom prop="str" size=30 tabindex=2}}
            {{/me_form_field}}

            {{me_form_field nb_cells=2 label="CPatient.IPP"}}
                <input tabindex="6" type="text" name="patient_ipp" value="{{$patient->_IPP}}"/>
            {{/me_form_field}}
        </tr>

        <tr>
            <td>
                {{if $app->user_prefs.LogicielLectureVitale == 'vitaleVision'}}
                    <button class="search singleclick" type="button"
                            onclick="$V(this.form.useVitale, 1); VitaleVision.read();">
                        {{tr}}CPatient.read_card_vitaleVision{{/tr}}
                    </button>
                {{elseif $app->user_prefs.LogicielLectureVitale == 'mbHost'}}
                    {{mb_include module=mbHost template=inc_vitale operation='search' formName='patientSearch'}}
                {{elseif $modFSE && $modFSE->canRead()}}
                    {{mb_include module=fse template=inc_button_vitale}}
                {{/if}}
            </td>
            <td class="button">
                <button class="search me-primary" id="pat_selector_search_pat_button"
                        type="submit">{{tr}}Search{{/tr}}</button>
                <button class="erase me-tertiary" type="button" onclick="PatSelector.reset(this.form)" title="{{tr}}Empty{{/tr}}">{{tr}}Empty{{/tr}}</button>
            </td>
            <td class="button">
                {{if $can->edit}}
                    <button class="new me-secondary" id="vw_idx_patient_button_create" type="button"
                            style="display: none;"
                            onclick="PatientFromSelector.create({{$useVitale}});">
                        {{tr}}Create{{/tr}}
                    </button>
                {{/if}}
            </td>
            <td class="button">
                <button class="cancel me-tertiary me-dark" type="button"
                        onclick="Control.Modal.close()">{{tr}}Cancel{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>

<div id="patients-list"></div>
