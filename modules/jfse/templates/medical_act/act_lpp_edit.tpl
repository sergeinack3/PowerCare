{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=act_guid value=$act->_guid}}
{{mb_script module=jfse script=ThirdPartyPayment ajax=$ajax}}

<script type="text/javascript">
    Main.add(function() {
        MedicalActs.initializeEditView(getForm('edit{{$act->_guid}}'), '{{$execution_date_min}}');
    });
</script>

<form name="edit{{$act->_guid}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_class object=$act}}
    {{mb_key object=$act}}

    <input type="hidden" name="del" value="0">
    <input type="hidden" name="_ignore_eai_handlers" value="1">

    {{mb_field object=$act field=object_id hidden=true}}
    {{mb_field object=$act field=object_class hidden=true}}

    <table class="form">
        <tr>
            <th class="title" colspan="2">
                {{mb_include module=system template=inc_object_idsante400 object=$act}}
                {{mb_include module=system template=inc_object_history object=$act}}
                {{$act->code}} :
                <span style="font-weight: normal;">{{$act->_code_lpp->name}}</span>
            </th>
        </tr>
        <tr>
            {{me_form_field nb_cells=1 mb_object=$act mb_field=code_prestation class='halfPane button'}}
                {{mb_field object=$act field=code_prestation disabled="disabled" size=3}}
            {{/me_form_field}}
            {{me_form_field nb_cells=1 mb_object=$act mb_field=type_prestation class='halfPane button'}}
                {{mb_field object=$act field=type_prestation disabled="disabled" emptyLabel='Select'}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=execution}}
                {{mb_field object=$act field=execution form="edit$act_guid" register=true}}
            {{/me_form_field}}
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=executant_id}}
                {{mb_field object=$act field=executant_id hidden=true}}
                <input type="text" name="_executant_view" class="autocomplete me-w100px" value="{{if $act->_ref_executant}}{{$act->_ref_executant}}{{/if}}"/>
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=quantite}}
                {{mb_field object=$act field=quantite onchange="MedicalActs.LppAct.updateAmounts(this.form);"}}
            {{/me_form_field}}
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=qualif_depense}}
                <select name="qualif_depense" >
                    <option value="" {{if !$act->qualif_depense}} selected="selected"{{/if}}>{{tr}}CActeLPP.qualif_depense.{{/tr}}</option>
                    {{foreach from=$act->_qual_depense item=_qualif}}
                        <option value="{{$_qualif}}"{{if $_qualif == $act->qualif_depense}} selected="selected"{{/if}}{{if $_qualif|in_array:$act->_unauthorized_qual_depense}} disabled="disabled"{{/if}}>
                            {{tr}}CActeLPP.qualif_depense.{{$_qualif}}{{/tr}}
                        </option>
                    {{/foreach}}
                </select>
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=montant_base}}
                {{mb_field object=$act field=montant_base readonly="readonly"}}
            {{/me_form_field}}
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=montant_final}}
                {{mb_field object=$act field=montant_final readonly="readonly"}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=montant_depassement}}
                {{mb_field object=$act field=montant_depassement onchange="MedicalActs.LppAct.updateAmounts(this.form);"}}
            {{/me_form_field}}
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=montant_total}}
                {{mb_field object=$act field=montant_total}}
            {{/me_form_field}}
        </tr>
        {{if $act->_dep}}
            <tr>
                {{me_form_field nb_cells=2 mb_object=$act mb_field=accord_prealable layout=true}}
                    <i id="info_dep" class="fa fa-lg fa-exclamation-circle" style="color: #{{if $act->accord_prealable && $act->date_demande_accord && $act->reponse_accord}}197837{{else}}ffa30c{{/if}};" title="{{tr}}CActeCCAM-msg-dep{{/tr}}"></i>
                    {{mb_field object=$act field=accord_prealable onchange="MedicalActs.toggleDateDEP(this); MedicalAct.checkDEP(this.form);"}}
                {{/me_form_field}}
            </tr>
            <tr id="accord_prealable-details-row"{{if !$act->accord_prealable}} style="display: none;"{{/if}}>
                {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=date_demande_accord}}
                    {{mb_field object=$act field=date_demande_accord form="edit$act_guid" register=true onchange="MedicalAct.NgapAct.checkDEP(this.form);"}}
                {{/me_form_field}}
                {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=reponse_accord}}
                    {{mb_field object=$act field=reponse_accord emptyLabel='Select' onchange="MedicalActs.checkDEP(this.form);"}}
                {{/me_form_field}}
            </tr>
        {{/if}}
        <tr>
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=date}}
                {{mb_field object=$act field=date register=true form="edit$act_guid"}}
            {{/me_form_field}}
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=date_fin}}
                {{mb_field object=$act field=date_fin register=true form="edit$act_guid"}}
            {{/me_form_field}}
        </tr>
    </table>
</form>

{{mb_include module=jfse template=medical_act/amo_amount_forcing}}

{{if $third_party_amc}}
    {{mb_include module=jfse template=medical_act/amc_amount_forcing}}

    <fieldset>
        <legend>
            <span onclick="MedicalActs.toggleView(this, 'formulas');" style="cursor: pointer; float: right; margin-right: 5px;">
                <i class="fa-chevron-right fa fa-g"></i>
            </span>
            {{tr}}CJfseActView-title-formula{{/tr}}
        </legend>
        <form name="edit{{$act->_guid}}-AMC_Formula" method="post" action="?" onsubmit="return false;">
            <table class="form" id="formulas-container" style="display: none;">
                {{mb_include module=jfse template=invoicing/formulas_list selected_formula=$medical_act->formula}}
            </table>
        </form>
    </fieldset>
{{/if}}

<div style="width: 100%; text-align: center; margin-bottom: 5px;">
    <button type="button" class="save" onclick="MedicalActs.store('{{$act->_guid}}');">{{tr}}Save{{/tr}}</button>
    <button type="button" class="trash" onclick="MedicalActs.delete('{{$act->_guid}}');">{{tr}}Delete{{/tr}}</button>
</div>
