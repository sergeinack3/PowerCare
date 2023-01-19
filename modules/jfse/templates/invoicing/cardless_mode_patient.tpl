{{*
 * @package Mediboard\Jse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=CardlessPatient ajax=true}}

<script type="text/javascript">
    Main.add(function() {
      CardlessPatient.initializeView('{{$consultation_id}}', '{{$patient->_guid}}', '{{$securing_mode}}')
    });
</script>

{{if $set_patient_data}}
    <form name="edit-{{$patient->_guid}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$patient}}
        {{mb_key object=$patient}}

        <fieldset>
            <legend>
                {{tr}}Beneficiary{{/tr}}
            </legend>
            <table class="form">
                <tr>
                    {{me_form_field nb_cells=1 mb_object=$patient mb_field=nom_jeune_fille}}
                        {{mb_field object=$patient field=nom_jeune_fille}}
                    {{/me_form_field}}
                    {{me_form_field nb_cells=1 mb_object=$patient mb_field=prenom}}
                        {{mb_field object=$patient field=prenom}}
                    {{/me_form_field}}
                </tr>
                <tr>
                    {{me_form_field nb_cells=1 mb_object=$patient mb_field=naissance}}
                        {{mb_field object=$patient field=naissance}}
                    {{/me_form_field}}
                    {{me_form_field nb_cells=1 mb_object=$patient mb_field=rang_naissance}}
                        {{mb_field object=$patient field=rang_naissance canNull=false}}
                    {{/me_form_field}}
                </tr>
                <tr>
                    {{me_form_field nb_cells=1 mb_object=$patient mb_field=qual_beneficiaire}}
                        {{mb_field object=$patient field=qual_beneficiaire canNull=false}}
                    {{/me_form_field}}
                    {{me_form_field nb_cells=1 mb_object=$patient mb_field=matricule}}
                        {{mb_field object=$patient field=matricule}}
                    {{/me_form_field}}
                </tr>
            </table>
        </fieldset>
        <fieldset>
            <legend>
                {{tr}}Insured{{/tr}}
            </legend>
            <table class="form">
                <tr>
                    {{me_form_field nb_cells=1 mb_object=$patient mb_field=assure_matricule}}
                        {{mb_field object=$patient field=assure_matricule canNull=false}}
                    {{/me_form_field}}
                    {{me_form_field nb_cells=1}}
                        <select name="code_regime" class="notNull">
                            <option value="">&mdash; {{tr}}Select{{/tr}}</option>
                            {{foreach from=$regimes item=regime}}
                                <option value="{{$regime.code}}"{{if $patient->code_regime == $regime.code}} selected{{/if}}>
                                    {{$regime.label}}
                                </option>
                            {{/foreach}}
                        </select>
                        <label for="edit-{{$patient->_guid}}_code_regime" title="{{tr}}CPatient-code_regime-desc{{/tr}}">{{tr}}CPatient-code_regime{{/tr}}</label>
                    {{/me_form_field}}
                </tr>
                <tr>
                    {{me_form_field nb_cells=1 label=CPatientVitalCard-organism title_label=CPatientVitalCard-organism-desc}}
                        <input type="text" name="organism_label" value="{{if $patient_organism}}{{$patient_organism.label}}{{/if}}">
                        <div style="width: 100%; display: flex; flex-flow: row wrap; align-items: center;">
                            {{me_form_field mb_object=$patient mb_field=caisse_gest}}
                                {{mb_field object=$patient field=caisse_gest}}
                            {{/me_form_field}}
                            {{me_form_field mb_object=$patient mb_field=centre_gest}}
                                {{mb_field object=$patient field=centre_gest}}
                            {{/me_form_field}}
                        </div>
                    {{/me_form_field}}
                    {{me_form_field nb_cells=1}}
                        <select name="code_gestion" class="notNull">
                            <option value="">&mdash; {{tr}}Select{{/tr}}</option>
                            <option value="00">Inconnu</option>
                            {{foreach from=$managing_codes item=code}}
                                <option value="{{$code.code}}"{{if $patient->code_gestion == $code.code}} selected{{/if}}>
                                    {{$code.label}}
                                </option>
                            {{/foreach}}
                        </select>
                        <label for="edit-{{$patient->_guid}}_code_gestion" title="{{tr}}CPatient-code_gestion-desc{{/tr}}">{{tr}}CPatient-code_gestion{{/tr}}</label>
                    {{/me_form_field}}
                </tr>
            </table>
        </fieldset>
    </form>
{{/if}}

<form name="selectCodeSituation" method="post" action="?" onsubmit="return false;">
    <div id="situation_code_container" style="margin-top: 10px; padding-left: 10px;">
        {{mb_include module=jfse template='invoicing/situation_code_field' nb_cells=0}}
    </div>
</form>

<div style="width: 100%; text-align: center; margin: 5px 0px 5px;">
    <button type="button" class="save" onclick="CardlessPatient.submit();">{{tr}}Validate{{/tr}}</button>
    <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
</div>
