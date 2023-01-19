{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=correspondant ajax=true}}
{{if !$dialog}}
    {{mb_script module=patients script=medecin ajax=true}}
{{/if}}

{{assign var=edit_for_admin value="dPpatients CMedecin edit_for_admin"|gconf}}

<script>
    Main.add(function () {
        Control.Tabs.create("tabs_correspondants", true);

        {{if $medecin_id && ($can->admin || !$edit_for_admin)}}
        Medecin.editMedecin('{{$medecin_id}}', refreshPageMedecin.curry('0'));
        {{else}}
        refreshPageMedecin();
        {{/if}}

        {{if $correspondant_id}}
        Correspondant.edit('{{$correspondant_id}}', null, refreshPageCorrespondant);
        {{else}}
        refreshPageCorrespondant();
        {{/if}}
    });

    refreshPageMedecin = function (page) {
        var oform = getForm('find_medecin');
        if (oform) {
            $V(oform.start_med, page);
            oform.onsubmit();
        }
    };

    refreshPageCorrespondant = function (page) {
        var oform = getForm('find_correspondant');
        if (oform) {
            $V(oform.start_corres, page);
            oform.onsubmit();
        }
    };

    onMergeComplete = function () {
        getForm("find_medecin").onsubmit();
    };

    popupExportCorrespondants = function () {
        var url = new Url("patients", "export_correspondants_medicaux");
        url.requestModal(600, 130);
    };

    popupImportCorrespondants = function () {
        var url = new Url("patients", "vw_import_correspondants_medicaux_csv");
        url.requestModal("80%", "80%", "Import de correspondants médicaux");
    };
    popupImportCorrespondantsPatients = function () {
        var url = new Url("patients", "import_correspondants_patient_csv");
        url.popup(800, 600, "Import de correspondants patient");
    };
</script>

<ul id="tabs_correspondants" class="control_tabs me-margin-top-2">
    <li>
        <a href="#medicaux">{{tr}}CCorrespondant-tab-medecin{{/tr}}</a>
    </li>
    {{if !$dialog || $all_correspondants}}
        <li>
            <a href="#autres">{{tr}}CCorrespondant-tab-others{{/tr}}</a>
        </li>
    {{/if}}
</ul>

<div id="medicaux" style="display: none;">
    <form name="find_medecin" action="?" method="get" onsubmit="return onSubmitFormAjax(this, null, 'medicaux_result')">
        <input type="hidden" name="m" value="{{$m}}"/>
        <input type="hidden" name="a" value="listMedecins"/>
        <input type="hidden" name="dialog" value="{{$dialog}}"/>
        <input type="hidden" name="annuaire" value="0"/>
        <input type="hidden" name="view_update" value="{{$view_update}}"/>
        <input type="hidden" name="start_med" value="{{$start_med}}"/>
        <input type="hidden" name="step_med" value="{{$step_med}}"/>

        <table class="main form me-no-box-shadow me-margin-top-0">
            <tr>
                <th class="title me-no-title me-no-bg me-padding-0" colspan="6">{{tr}}CMedecin.search{{/tr}}</th>
            </tr>

            <tr>
                {{me_form_field nb_cells=2 mb_object=$medecin mb_field="nom"}}
                {{mb_field object=$medecin field=nom prop=str tabindex=1
                onchange="\$V(this.form.start_med, 0)" style="width: 13em;" name="medecin_nom"}}
                {{/me_form_field}}

                {{me_form_field nb_cells=2 mb_object=$medecin mb_field=cp}}
                {{mb_field object=$medecin field=cp prop=str tabindex=3
                onchange="\$V(this.form.start_med, 0)" style="width: 13em;" name="medecin_cp"}}
                {{/me_form_field}}

                {{me_form_field nb_cells=2 mb_object=$medecin mb_field=type}}
                {{mb_field object=$medecin field=type emptyLabel="All" tabindex=5
                onchange="\$V(this.form.start_med, 0)" style="width: 13em;"}}
                {{/me_form_field}}
            </tr>

            <tr>
                {{me_form_field nb_cells=2 mb_object=$medecin mb_field="prenom"}}
                {{mb_field object=$medecin field=prenom prop=str tabindex=2
                onchange="\$V(this.form.start_med, 0)" style="width: 13em;" name="medecin_prenom"}}
                {{/me_form_field}}

                {{me_form_field nb_cells=2 mb_object=$medecin mb_field=ville}}
                {{mb_field object=$medecin field=ville prop=str tabindex=4
                onchange="\$V(this.form.start_med, 0)" style="width: 13em;" name="medecin_ville"}}
                {{/me_form_field}}

                {{me_form_field nb_cells=2 mb_object=$medecin mb_field="disciplines"}}
                {{mb_field object=$medecin field=disciplines prop=str tabindex=6
                onchange="\$V(this.form.start_med, 0)" style="width: 13em;"}}
                {{/me_form_field}}
            </tr>

            {{if $is_admin}}
                <tr>
                    <th></th>
                    <td></td>
                    <th></th>
                    <td></td>
                    {{me_form_field nb_cells=2 mb_object=$medecin mb_field="function_id"}}
                        <select name="function_id" style="width: 13em;" onchange="\$V(this.form.start_med, 0)">
                            <option value="">&mdash; Toutes</option>
                            {{foreach from=$listFunctions item=_function}}
                                <option value="{{$_function->_id}}"
                                        {{if $_function->_id == $medecin->function_id}}selected="selected"{{/if}}>
                                    {{$_function}}
                                </option>
                            {{/foreach}}
                        </select>
                    {{/me_form_field}}
                </tr>
            {{/if}}

            <tr>
                {{me_form_field nb_cells=2 mb_object=$medecin mb_field=rpps}}
                {{mb_field object=$medecin field=rpps prop=str tabindex=2
                onchange="\$V(this.form.start_med, 0)" style="width: 13em;"}}
                {{/me_form_field}}
                <th></th>
                <td></td>
                <th>{{mb_label object=$medecin field=actif}}</th>
                <td>
                  <input type="radio" name="actif" value="1" checked class="default|1" id="find_medecin_actif_1">
                  <label for="find_medecin_actif_1" id="labelFor_find_medecin_actif_1">{{tr}}bool.1{{/tr}}</label>
                  <input type="radio" name="actif" value="0" id="find_medecin_actif_0">
                  <label for="find_medecin_actif_0" id="labelFor_find_medecin_actif_0">{{tr}}bool.0{{/tr}}</label>
                  <input type="radio" name="actif" value="2" id="find_medecin_actif_2">
                  <label for="find_medecin_actif_2" id="labelFor_find_medecin_actif_2">{{tr}}All{{/tr}}</label>
                </td>
            </tr>

            <tr>
                <td class="button" colspan="6">
                    {{if !$dialog}}
                        <button class="search me-primary" type="submit"
                                onclick="$V(this.form.annuaire, 0);">{{tr}}Search{{/tr}}</button>
                    {{else}}
                        <button id="vw_medecins_button_dialog_search" class="search me-primary" type="submit"
                                onclick="$V(this.form.annuaire, 0); formVisible=false;">
                            {{tr}}Search{{/tr}}
                        </button>
                    {{/if}}
                    {{if $can->admin || !$edit_for_admin}}
                        <button class="new" type="button"
                                onclick="Correspondant.openCorrespondantImportFromRPPSModal()">{{tr}}Add{{/tr}}</button>
                        {{if $is_admin}}
                            <button type="button" class="download me-tertiary"
                                    onclick="popupExportCorrespondants();">{{tr}}Export-CSV{{/tr}}</button>
                            <button type="button" class="import me-tertiary" onclick="popupImportCorrespondants();"
                                    title="Importer des correspondants médicaux">{{tr}}Import{{/tr}}</button>
                        {{/if}}
                    {{/if}}
                </td>
            </tr>
        </table>
    </form>
    <hr class="me-no-display"/>
    <div id="medicaux_result"></div>
</div>

{{if !$dialog || $all_correspondants}}
    <div id="autres" style="display: none;">
        <form name="find_correspondant" action="?" method="get"
              onsubmit="return onSubmitFormAjax(this, null, 'correspondants_result')">
            <input type="hidden" name="m" value="{{$m}}"/>
            <input type="hidden" name="a" value="ajax_list_correspondants_modele"/>
            <input type="hidden" name="dialog" value="{{$dialog}}"/>
            <input type="hidden" name="start_corres" value="{{$start_corres}}"/>
            <input type="hidden" name="step_corres" value="{{$step_corres}}"/>

            <table class="form me-no-box-shadow me-margin-top-0">
                <tr>
                    <th class="title me-no-title me-no-bg" colspan="6">{{tr}}CCorrespondantPatient.search{{/tr}}
                        (Modèles)
                    </th>
                </tr>

                <tr>
                    {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="nom"}}
                    {{mb_field object=$correspondant field=nom prop=str tabindex=1
                    onchange="\$V(this.form.start_corres, 0)" style="width: 13em;"}}
                    {{/me_form_field}}

                    {{me_form_field nb_cells=2 mb_object=$correspondant  mb_field="cp"}}
                    {{mb_field object=$correspondant field=cp prop=str tabindex=4
                    onchange="\$V(this.form.start_corres, 0)" style="width: 13em;"}}
                    {{/me_form_field}}

                    {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="relation"}}
                    {{mb_field object=$correspondant field=relation emptyLabel="All" tabindex=6
                    onchange="\$V(this.form.start_corres, 0)" style="width: 13em;"}}
                    {{/me_form_field}}
                </tr>

                <tr>
                    {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="prenom"}}
                    {{mb_field object=$correspondant field=prenom prop=str tabindex=2
                    onchange="\$V(this.form.start_corres, 0)" style="width: 13em;"}}
                    {{/me_form_field}}

                    {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="ville"}}
                    {{mb_field object=$correspondant field=ville prop=str tabindex=5
                    onchange="\$V(this.form.start_corres, 0)" style="width: 13em;"}}
                    {{/me_form_field}}

                    {{if $is_admin}}
                        {{me_form_field nb_cells=6 mb_object=$correspondant mb_field="function_id"}}
                            <select name="function_id" style="width: 13em;" onchange="\$V(this.form.start_corres, 0)"
                                    tabindex="7">
                                <option value="">&mdash; Toutes</option>
                                {{foreach from=$listFunctions item=_function}}
                                    <option value="{{$_function->_id}}"
                                            {{if $_function->_id == $correspondant->function_id}}selected="selected"{{/if}}>
                                        {{$_function}}
                                    </option>
                                {{/foreach}}
                            </select>
                        {{/me_form_field}}
                    {{else}}
                        <th></th>
                        <td></td>
                    {{/if}}
                </tr>
                <tr>
                    {{me_form_field nb_cells=2 mb_object=$correspondant mb_field="surnom"}}
                    {{mb_field object=$correspondant field=surnom prop=str tabindex=3
                    onchange="\$V(this.form.start_corres, 0)" style="width: 13em;"}}
                    {{/me_form_field}}
                    <th></th>
                    <td></td>
                    <th></th>
                    <td></td>
                </tr>
                <tr>
                    <td class="button" colspan="6">
                        <button class="search me-primary" type="submit">{{tr}}Search{{/tr}}</button>
                        <button class="new" type="button"
                                onclick="Correspondant.edit('0', null, refreshPageCorrespondant)">{{tr}}Create{{/tr}}</button>
                        <button class="import me-tertiary" type="button" onclick="popupImportCorrespondantsPatients();"
                                title="Importer des correspondants patient">{{tr}}Import{{/tr}}</button>
                    </td>
                </tr>
            </table>
        </form>
        <hr class="me-no-display"/>
        <div id="correspondants_result"></div>
    </div>
{{/if}}
