{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients    script=recherche_doc}}
{{mb_script module=patients    script=pat_selector}}
{{mb_script module=compteRendu script=document}}

<script>
    Main.add(function () {
        var form = RechercheDoc.form = getForm('filterDocs');
        Calendar.regField(form.date_min);
        Calendar.regField(form.date_max);

        new Url('mediusers', 'ajax_users_autocomplete')
            .addParam('edit', '1')
            .addParam('input_field', 'user_id_view')
            .autoComplete(form.user_id_view, null, {
                minChars: 0,
                method: 'get',
                select: 'view',
                dropdown: true,
                afterUpdateElement: function (field, selected) {
                    var id = selected.getAttribute('id').split('-')[2];
                    $V(form.user_id, id);
                }
            });

        new Url('mediusers', 'ajax_users_autocomplete')
            .addParam('edit', '1')
            .addParam('praticiens', 1)
            .addParam('input_field', 'praticien_id_view')
            .autoComplete(form.praticien_id_view, null, {
                minChars: 0,
                method: 'get',
                select: 'view',
                dropdown: true,
                afterUpdateElement: function (field, selected) {
                    var id = selected.getAttribute('id').split('-')[2];
                    $V(form.praticien_id, id);
                }
            });

        // Pat Selector
        PatSelector.init = function () {
            this.sForm = 'filterDocs';
            this.sId = 'patient_id_search';
            this.sView = '_pat_name';
            this.pop();
        };

        RechercheDoc.listDocs('{{$page}}');
    });
</script>

<form name="delDocs" method="post">
    {{mb_class class=CCompteRendu}}
    <input type="hidden" name="compte_rendu_ids"/>
    <input type="hidden" name="del" value="1"/>
</form>

<form name="filterDocs" method="get">
    <fieldset class="me-align-auto">
        <legend>{{tr}}filters{{/tr}}</legend>

        <table class="form me-no-box-shadow">
            <tr>
                {{me_form_field nb_cells=2 label="Dates"}}
                    <div>
                        {{mb_field object=$compte_rendu field="_date_min" form=filterDocs register=true}}
                        &gt;&gt;&gt;
                        {{mb_field object=$compte_rendu field="_date_max" form=filterDocs register=true}}
                    </div>
                {{/me_form_field}}

                {{me_form_field nb_cells=2 label="common-Context"}}
                {{assign var=_spec value=$compte_rendu->_specs.object_class}}
                    <select name="object_class" onchange="RechercheDoc.filterByContext(this.value);">
                        <option value="">&mdash; {{tr}}CCompteRendu-object_class-all{{/tr}}</option>
                        {{foreach from=$_spec->_locales item=_locale key=_object_class}}
                            {{if in_array($_object_class, array("CConsultation", "CConsultAnesth", "COperation", "CPatient", "CSejour", 'CEvenementPatient'))}}
                                <option value="{{$_object_class}}"
                                        {{if $_object_class === $object_class}}selected{{/if}}>{{$_locale}}</option>
                            {{/if}}
                        {{/foreach}}
                    </select>
                {{/me_form_field}}

                {{me_form_field nb_cells=2 label="Owner"}}
                    <input type="hidden" name="user_id" value="{{$user->_id}}"/>
                    <input type="text" name="user_id_view" value="{{$user}}"/>
                    <button type="button" class="cancel notext me-tertiary me-dark"
                            onclick="$V(this.form.user_id, ''); $V(this.form.user_id_view, '');"></button>
                {{/me_form_field}}
            </tr>
            <tr>
                {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="_status"}}
                {{mb_field object=$compte_rendu field="_status" emptyLabel="All"}}
                {{/me_form_field}}

                {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="_nom"}}
                {{mb_field object=$compte_rendu field="_nom"}}
                {{/me_form_field}}

                {{me_form_field nb_cells=2 label="CMediusers-in_charge"}}
                    <input type="hidden" name="praticien_id" value="{{$praticien->_id}}"/>
                    <input type="text" name="praticien_id_view" value="{{$praticien}}"/>
                    <button type="button" class="cancel notext me-tertiary me-dark"
                            onclick="$V(this.form.praticien_id, ''); $V(this.form.praticien_id_view, '');"></button>
                {{/me_form_field}}
            </tr>

            <tr>
                <td colspan="2"></td>
                {{me_form_field nb_cells=2 label="CCompteRendu-_is_locked"}}
                    <select name="_is_locked">
                        <option value="all" selected>&mdash; {{tr}}All{{/tr}}</option>
                        <option value="0">{{tr}}No{{/tr}}</option>
                        <option value="1">{{tr}}Yes{{/tr}}</option>
                    </select>
                {{/me_form_field}}
                {{me_form_field nb_cells=2 mb_class=CConsultation mb_field=patient_id}}
                    <input type="hidden" name="patient_id_search" value="{{$patient->_id}}"
                           ondblclick="PatSelector.init();"/>
                    <input type="text" name="_pat_name" style="width: 15em;" value="{{$patient}}" readonly
                           onfocus="PatSelector.init()"/>
                    <button class="search notext me-tertiary" type="button"
                            onclick="PatSelector.init();">{{tr}}Search{{/tr}}</button>
                    <button class="cancel notext me-tertiary me-dark" type="button"
                            onclick="$V(this.form.patient_id_search, ''); $V(this.form._pat_name, '');">{{tr}}Delete{{/tr}}</button>
                {{/me_form_field}}
            </tr>

            <tr>
                <td colspan="8" class="button">
                    <button type="button" class="search me-primary"
                            onclick="RechercheDoc.listDocs(0);">{{tr}}Filter{{/tr}}</button>
                </td>
            </tr>
        </table>
    </fieldset>

    <fieldset id="filtre_CSejour" class="filtres_context"
              {{if $object_class !== "CSejour"}}style="display: none;"{{/if}}>
        <legend>
            {{tr}}filters{{/tr}} {{tr}}CSejour{{/tr}}
        </legend>

        <table class="form me-no-box-shadow">
            <tr>
                {{me_form_field nb_cells=2 label="CSejour-type"}}
                {{mb_field class=CSejour field=type emptyLabel=All value=$type}}
                {{/me_form_field}}

                {{me_form_field nb_cells=2 mb_class=CSejour mb_field=service_id}}
                    <select name="service_id">
                        <option value="">&mdash; {{tr}}All{{/tr}}</option>
                        {{foreach from=$services item=_service}}
                            <option value="{{$_service->_id}}">{{$_service}}</option>
                        {{/foreach}}
                    </select>
                {{/me_form_field}}
            </tr>
        </table>
    </fieldset>
</form>

<fieldset class="me-align-auto me-no-border-radius-top">
    <legend>{{tr}}CCompteRendu-Actions for selection{{/tr}}</legend>

    <button type="button" class="edit me-tertiary" onclick="RechercheDoc.openDocs();">{{tr}}Modify{{/tr}}</button>
    <button type="button" class="print me-tertiary" onclick="RechercheDoc.printDocs();">{{tr}}Print{{/tr}}</button>
    {{if $can->admin}}
        <button type="button" class="trash me-tertiary"
                onclick="RechercheDoc.deleteDocs();">{{tr}}Delete{{/tr}}</button>
    {{/if}}
    <button type="button" class="mail me-tertiary"
            onclick="RechercheDoc.sendDocs();">{{tr}}CCompteRendu.send_mail{{/tr}}</button>
    {{if "apicrypt"|module_active}}
        {{mb_include module=apicrypt template=inc_button_send_mail}}
    {{/if}}
    <button type="button" class="download me-tertiary" onclick="RechercheDoc.downloadDocs()">{{tr}}Download{{/tr}}</button>
</fieldset>

<div id="docs_area" class="me-padding-0"></div>
