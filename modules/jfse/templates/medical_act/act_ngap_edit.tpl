{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=act_guid value=$act->_guid}}
{{mb_script module=jfse script=ThirdPartyPayment ajax=$ajax}}

<script type="text/javascript">
    Main.add(function() {
        MedicalActs.initializeEditView(getForm('edit{{$act->_guid}}'), '{{$execution_date_min}}');

        {{foreach from=$act->_forbidden_complements item=_complement}}
            var options = $$('form[name="edit{{$act->_guid}}"] select[name="complement"] option[value="{{$_complement}}"]');
            options.each(function(option) {
                option.writeAttribute('disabled', 'disabled');
            });
        {{/foreach}}
    });
</script>

<form name="edit{{$act->_guid}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this);">
    {{mb_class object=$act}}
    {{mb_key object=$act}}
    <input type="hidden" name="m" value="cabinet">
    <input type="hidden" name="dosql" value="do_acte_ngap_aed">
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
                <span style="font-weight: normal;">{{$act->_libelle}}</span>
            </th>
        </tr>
        <tr>
            {{me_form_field nb_cells=2 mb_object=$act mb_field=montant_base class='button'}}
                {{mb_field object=$act field=montant_base readonly="readonly" size=3}}
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
                {{mb_field object=$act field=quantite}}
            {{/me_form_field}}
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=coefficient}}
                {{mb_field object=$act field=coefficient}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=complement}}
                {{mb_field object=$act field=complement emptyLabel='Select'}}
            {{/me_form_field}}
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=lieu}}
                {{mb_field object=$act field=lieu}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=demi}}
                {{mb_field object=$act field=demi}}
            {{/me_form_field}}
            {{if $act->isIKInfirmier()}}
                {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=taux_abattement layout=true}}
                    <select name="taux_abattement" onchange="MedicalActs.NgapAct.changeTauxAbattement(this);" style="margin-left: 10px;">
                        <option value="1.00"{{if $act->taux_abattement == 1}} selected="selected"{{/if}}>0%</option>
                        <option value="0.50"{{if $act->taux_abattement == 0.5}} selected="selected"{{/if}}>50%</option>
                        <option value="0"{{if $act->taux_abattement == 0}} selected="selected"{{/if}}>100%</option>
                    </select>
                    <i class="fa fa-info-circle" style="color: blue;" title="{{tr}}CActeNGAP-msg-taux_abattement{{/tr}}"></i>
                {{/me_form_field}}
            {{else}}
                <td></td>
            {{/if}}
        </tr>
        <tr>
            {{mb_ternary var=exoneration_class test=$act->_ref_object->concerne_ALD value='button' other=''}}
            {{mb_ternary var=exoneration_cells test=$act->_ref_object->concerne_ALD value=1 other=2}}
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=exoneration class=$exoneration_class}}
                {{mb_field object=$act field=exoneration}}
            {{/me_form_field}}
            {{if $act->_ref_object->concerne_ALD}}
                {{me_form_bool class='halfpane' nb_cells=1 mb_object=$act mb_field=ald}}
                    {{mb_field object=$act field=ald typeEnum=checkbox}}
                {{/me_form_bool}}
            {{else}}
                <td></td>
            {{/if}}
        </tr>
        <tr>
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=montant_depassement}}
                {{mb_field object=$act field=montant_depassement}}
            {{/me_form_field}}
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=qualif_depense}}
                {{mb_field object=$act field=qualif_depense emptyLabel='Select'}}
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
