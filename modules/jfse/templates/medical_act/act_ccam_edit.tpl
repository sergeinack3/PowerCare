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
    });

    syncDentField = function(input) {
        let dents = $V(input.form.position_dentaire);
        let num_dent = input.getAttribute('data-localisation');

        if (dents != '') {
            dents = dents.split('|');
        }
        else {
            dents = [];
        }

        if (input.checked) {
            dents.push(num_dent);
        }
        else if (!input.checked && dents.indexOf(num_dent) != -1) {
            dents.splice(dents.indexOf(num_dent), 1);
        }

        $('checked_teeth-{{$act->_guid}}').innerHTML = dents.length;
        $V(input.form.elements['count_teeth_checked'], dents.length);
        if (dents.length != parseInt('{{$phase->nb_dents}}')) {
            $('checked_teeth-{{$act->_guid}}').setStyle({color: 'firebrick'});
        }
        else {
            $('checked_teeth-{{$act->_guid}}').setStyle({color: 'forestgreen'});
        }

        $V(input.form.position_dentaire, dents.join('|'));
    };
</script>

<form name="edit{{$act->_guid}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this);">
    <input type="hidden" name="m" value="salleOp" />
    <input type="hidden" name="dosql" value="do_acteccam_aed" />
    <input type="hidden" name="del" value="0" />
    {{mb_key object=$act}}

    <input type="hidden" name="_ignore_eai_handlers" value="1">
    <input type="hidden" name="_calcul_montant_base" value="1" />
    <input type="hidden" name="_edit_modificateurs" value="1"/>

    {{mb_field object=$act field=object_id hidden=true}}
    {{mb_field object=$act field=object_class hidden=true}}
    {{mb_field object=$act field=code_acte hidden=true}}
    {{mb_field object=$act field=code_activite hidden=true}}
    {{mb_field object=$act field=code_phase hidden=true}}

    <table class="form" style="max-width: 840px;">
        <tr>
            <th class="title" colspan="2">
                {{mb_include module=system template=inc_object_idsante400 object=$act}}
                {{mb_include module=system template=inc_object_history object=$act}}
                <div style="max-width: 800px; text-overflow: ellipsis;">
                    {{$act->_ref_code_ccam->code}} :
                    <span style="font-weight: normal; overflow-wrap: break-word;">
                        {{$act->_ref_code_ccam->libelleLong}}
                    </span>
                </div>
                <span style="font-weight: normal">
                  <span title="Activité de l'acte">Activité {{$activity->numero}} ({{$activity->type}})</span> &mdash;
                  <span title="Phase de l'acte">Phase {{$phase->phase}}</span> &mdash;
                  <span title="Tarif de base de l'activité">{{$act->_tarif_base|currency}}</span>
                </span>
            </th>
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
            {{me_form_field nb_cells=2 mb_object=$act mb_field=modificateurs layout=true}}
                {{assign var=nb_modificateurs value=$act->modificateurs|strlen}}
                {{foreach from=$phase->_modificateurs item=_mod name=modificateurs}}
                    <span class="circled {{if $_mod->_state == 'prechecked'}}ok{{elseif $_mod->_checked && in_array($_mod->_state, array('not_recommended', 'forbidden'))}}error{{elseif in_array($_mod->_state, array('not_recommended', 'forbidden'))}}warning{{/if}}"
                          title="{{$_mod->libelle}} ({{$_mod->_montant}})">
                        <input type="checkbox" class="modificateur" data-code="{{$_mod->code}}" data-double="{{$_mod->_double}}" name="modificateur_{{$_mod->code}}{{$_mod->_double}}"
                        {{if $_mod->_checked}}checked="checked"{{elseif $nb_modificateurs == 4 || $_mod->_state == 'forbidden' || (intval($act->_exclusive_modifiers) > 0 && in_array($_mod->code, array('F', 'U', 'P', 'S')))}} readonly="readonly"{{/if}}
                        onchange="MedicalAct.CccamAct.checkModifiers(this);"/>
                        <label for="modificateur_{{$_mod->code}}{{$_mod->_double}}">
                            {{$_mod->code}}
                        </label>
                    </span>
                {{foreachelse}}
                    <em>{{tr}}None{{/tr}}</em>
                {{/foreach}}
            {{/me_form_field}}
        </tr>
        <tr>
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=code_association}}
                {{mb_field object=$act field=code_association emptyLabel="CActeCCAM.code_association."}}
            {{/me_form_field}}
            {{if $act->_ref_code_ccam->remboursement == 3}}
                {{assign var=rembourse_label value="CDatedCodeCCAM.remboursement.3"}}
                {{me_form_bool class='halfPane' nb_cells=1 label=$rembourse_label title_label=$rembourse_label}}
                    {{mb_field object=$act field=rembourse}}
                {{/me_form_bool}}
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
            {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=motif_depassement}}
                {{mb_field object=$act field=motif_depassement emptyLabel="Select"}}
            {{/me_form_field}}
        </tr>
        {{if $act->_ref_code_ccam->entente_prealable}}
            <tr>
                {{me_form_field nb_cells=2 mb_object=$act mb_field=accord_prealable layout=true}}
                    <i id="info_dep" class="fa fa-lg fa-exclamation-circle" style="color: #{{if $act->accord_prealable && $act->date_demande_accord && $act->reponse_accord}}197837{{else}}ffa30c{{/if}};" title="{{tr}}CActeCCAM-msg-dep{{/tr}}"></i>
                    {{mb_field object=$act field=accord_prealable onchange="MedicalActs.toggleDateDEP(this); MedicalAct.checkDEP(this.form);"}}
                {{/me_form_field}}
            </tr>
            <tr id="accord_prealable-details-row"{{if !$act->accord_prealable}} style="display: none;"{{/if}}>
                {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=date_demande_accord}}
                    {{mb_field object=$act field=date_demande_accord form="formEditFullActe-$view" register=true onchange="MedicalActs.checkDEP(this.form);"}}
                {{/me_form_field}}
                {{me_form_field class='halfPane' nb_cells=1 mb_object=$act mb_field=reponse_accord}}
                    {{mb_field object=$act field=reponse_accord emptyLabel='Select' onchange="MedicalActs.checkDEP(this.form);"}}
                {{/me_form_field}}
            </tr>
        {{/if}}
        {{if $phase->nb_dents}}
            {{assign var=teeth_checked value=0}}
            {{if is_countable($act->_dents) && $act->_dents|@count}}
                {{assign var=teeth_checked value=$act->_dents|@count}}
            {{/if}}
            <tr>
                {{me_form_field nb_cells=2 mb_object=$act mb_field=position_dentaire layout=true}}
                    {{mb_field object=$act field=position_dentaire hidden=true}}
                    <span id="edit{{$act->_guid}}-teeth_list">
                        {{foreach from=$act->_dents item=_tooth name=teeth_loop}}
                            {{$_tooth}}
                            {{if !$smarty.foreach.teeth_loop.last}}
                                &nbsp;&mdash;&nbsp;
                            {{/if}}
                        {{/foreach}}
                    </span><em style="margin-left: 5px;">(<span id="checked_teeth-{{$act->_guid}}">{{$teeth_checked}}</span> / {{$phase->nb_dents}})</em>
                    <span style="float: right;"><button type="button" class="edit notext" onclick="MedicalActs.CcamAct.editTeeth('{{$act->_guid}}');">{{tr}}CActeCCAM-action-set-teeth{{/tr}}</button></span>
                {{/me_form_field}}
            </tr>
        {{/if}}
    </table>
</form>

{{if $phase->nb_dents}}
    <div id="teeth-container-{{$act->_guid}}" style="display: none;">
        <form name="setTeeth-{{$act->_guid}}" method="post" action="?" onsubmit="return false;" data-concerned_teeth_number="{{$phase->nb_dents}}" data-act_guid="{{$act->_guid}}">
            <div class="me-margin-top-5 me-margin-bottom-5 me-text-align-center" style="width: 100%;">
              Dent(s) concernée(s) (<span id="modal-checked_teeth-{{$act->_guid}}">{{$teeth_checked}}</span> / {{$phase->nb_dents}} cochée(s))
            </div>
            {{mb_include module=salleOp template=inc_schema_dents_ccam liste_dents=$teeth_list phase=$phase acte=$act set_teeth_callback="MedicalActs.CcamAct.setTooth"}}
            <div class="me-margin-top-5 me-margin-bottom-5 me-text-align-center" style="width: 100%;">
                <button type="button" class="tick" onclick="MedicalActs.CcamAct.setTeeth(this.form, '{{$act->_guid}}');">
                    {{tr}}Validate{{/tr}}
                </button>
            </div>
        </form>
    </div>
{{/if}}

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
                {{mb_ternary var=selected_formula test=$medical_act->formula value=$medical_act->formula->formula_number other='052'}}
                {{mb_include module=jfse template=invoicing/formulas_list selected_formula_number=$medical_act->formula->formula_number}}
            </table>
        </form>
    </fieldset>
{{/if}}

<div style="width: 100%; text-align: center; margin-bottom: 5px;">
    <button type="button" class="save" onclick="MedicalActs.store('{{$act->_guid}}');">{{tr}}Save{{/tr}}</button>
    <button type="button" class="trash" onclick="MedicalActs.delete('{{$act->_guid}}');">{{tr}}Delete{{/tr}}</button>
</div>
