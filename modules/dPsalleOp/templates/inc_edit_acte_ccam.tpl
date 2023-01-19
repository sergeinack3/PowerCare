{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="view" value=$acte->_id}}
<script>
    checkModificateurs = function (input, acte) {
        var exclusive_modifiers = ['F', 'P', 'S', 'U'];
        var checkboxes = $$('form[name="' + input.form.name + '"] input.modificateur');
        var nb_checked = 0;
        var exclusive_modifier = '';
        var exclusive_modifier_checked = false;
        checkboxes.each(function (checkbox) {
            if (checkbox.checked) {
                nb_checked++;
                if (checkbox.get('double') == 2) {
                    nb_checked++;
                }
                if (exclusive_modifiers.indexOf(checkbox.get('code')) != -1) {
                    exclusive_modifier = checkbox.get('code');
                    exclusive_modifier_checked = true;
                }
            }
        });

        checkboxes.each(function (checkbox) {
            if (
              (!checkbox.checked && nb_checked == 4) ||
              (exclusive_modifiers.indexOf(exclusive_modifier) != -1 && exclusive_modifiers.indexOf(checkbox.get('code')) != -1 && !checkbox.checked && exclusive_modifier_checked)
            ) {
                checkbox.disabled = true;
            } else {
                checkbox.disabled = false;
            }
        });

        var container = input.up();
        if (input.checked == true && container.hasClassName('warning')) {
            container.removeClassName('warning');
            container.addClassName('error');
        } else if (input.checked == false && container.hasClassName('error')) {
            container.removeClassName('error');
            container.addClassName('warning');
        }
    };

    syncDentField = function (input) {
        var dents = $V(input.form.position_dentaire);
        var num_dent = input.getAttribute('data-localisation');

        if (dents != '') {
            dents = dents.split('|');
        } else {
            dents = [];
        }

        if (input.checked) {
            dents.push(num_dent);
        } else if (!input.checked && dents.indexOf(num_dent) != -1) {
            dents.splice(dents.indexOf(num_dent), 1);
        }

        $('checked_teeth-{{$view}}').innerHTML = dents.length;
        $V(input.form.elements['count_teeth_checked'], dents.length);
        if (dents.length != parseInt('{{$phase->nb_dents}}')) {
            $('checked_teeth-{{$view}}').setStyle({color: 'firebrick'});
        } else {
            $('checked_teeth-{{$view}}').setStyle({color: 'forestgreen'});
        }

        $V(input.form.position_dentaire, dents.join('|'));
    };

    toggleDateDAP = function (input) {
        if (input.value == 1) {
            $('accord_prealable').show();
        } else {
            $('accord_prealable').hide();
        }
    };

    checkDEP = function (form) {
        var element = $('info_dep');

        if (element != null) {
            if ($V(form.accord_prealable) == '1' && $V(form.date_demande_accord) && $V(form.reponse_accord)) {
                element.setStyle({color: '#197837'});
            } else {
                element.setStyle({color: '#ffa30c'});
            }
        }
    };

    submitFormAct = function (form) {
        {{if $phase->nb_dents}}
        if (parseInt($V(form.elements['count_teeth_checked'])) != parseInt('{{$phase->nb_dents}}')) {
            Modal.alert($T('CActeCCAM-error-incorrect_teeth_number_checked', '{{$phase->nb_dents}}'));
            return false;
        }
        {{/if}}
        return onSubmitFormAjax(form, {
            onComplete: function () {
                window.urlEditActe.modalObject.close();
            }
        });
    };
</script>

<form name="formEditFullActe-{{$view}}" method="post" enctype="multipart/form-data"
      onsubmit="return submitFormAct(this);">

    <input type="hidden" name="m" value="salleOp"/>
    <input type="hidden" name="dosql" value="do_acteccam_aed"/>
    <input type="hidden" name="del" value="0"/>
    {{mb_key object=$acte}}

    <input type="hidden" name="_calcul_montant_base" value="1"/>
    <input type="hidden" name="_edit_modificateurs" value="1"/>

    {{mb_field object=$acte field=object_id hidden=true}}
    {{mb_field object=$acte field=object_class hidden=true}}
    {{mb_field object=$acte field=code_acte hidden=true}}
    {{mb_field object=$acte field=code_activite hidden=true}}
    {{mb_field object=$acte field=code_phase hidden=true}}

    <table class="form" style="min-width: 400px;">
        <tr>
            <th class="title" colspan="10">
                {{mb_include module=system template=inc_object_idsante400 object=$acte}}
                {{mb_include module=system template=inc_object_history object=$acte}}
                {{$acte->_ref_code_ccam->code}} :
                <span style="font-weight: normal;">{{$acte->_ref_code_ccam->libelleLong}}</span>
                <br/>
                <span style="font-weight: normal;">
          <span title="Activité de l'acte">Activité {{$activite->numero}} ({{$activite->type}})</span> &mdash;
          <span title="Phase de l'acte">Phase {{$phase->phase}}</span> &mdash;
          <span title="Tarif de base de l'activité">{{$acte->_tarif_base|currency}}</span>
        </span>
            </th>
        </tr>

        <!-- Date d'execution -->
        <tr>
            <th>{{mb_label object=$acte field=execution}}</th>
            <td>{{mb_field object=$acte field=execution form="formEditFullActe-$view" register=true}}</td>
        </tr>

        <!-- Executant -->
        <tr>
            <th>{{mb_label object=$acte field=executant_id}}</th>
            <td>
                {{mb_ternary var=listExecutants test=$acte->_anesth value=$listAnesths other=$listChirs}}
                <select name="executant_id" class="{{$acte->_props.executant_id}}" style="width: 15em;">
                    <option value="">&mdash; Choisir un professionnel de santé</option>
                    {{mb_include module=mediusers template=inc_options_mediuser selected=$acte->executant_id list=$listExecutants}}
                </select>
            </td>
        </tr>

        <!-- Extension documentaire -->
        {{if $acte->_anesth}}
            <tr>
                <th>{{mb_label object=$acte field=extension_documentaire}}</th>
                <td>
                    {{mb_field object=$acte field=extension_documentaire emptyLabel="Choose"
                    canNull='dPccam codage doc_extension_mandatory'|gconf|ternary:false:true style="width: 15em;"}}
                </td>
            </tr>
        {{/if}}


        <!-- Modificateurs -->
        <tr>
            <th>{{mb_label object=$acte field=modificateurs}}</th>
            <td class="text" colspan="10">
                {{assign var=nb_modificateurs value=$acte->modificateurs|strlen}}
                {{foreach from=$phase->_modificateurs item=_mod name=modificateurs}}
                    <span
                      class="circled {{if $_mod->_state == 'prechecked'}}ok{{elseif $_mod->_checked && in_array($_mod->_state, array('not_recommended', 'forbidden'))}}error{{elseif in_array($_mod->_state, array('not_recommended', 'forbidden'))}}warning{{/if}}">
            <input type="checkbox" class="modificateur" data-code="{{$_mod->code}}" data-double="{{$_mod->_double}}"
                   name="modificateur_{{$_mod->code}}{{$_mod->_double}}"
                   {{if $_mod->_checked}}checked="checked"
                   {{elseif $nb_modificateurs == 4 || $_mod->_state == 'forbidden' || (intval($acte->_exclusive_modifiers) > 0 && in_array($_mod->code, array('F', 'U', 'P', 'S')))}}disabled="disabled"{{/if}}
                   onchange="checkModificateurs(this, '{{$acte->code_acte}}');"/>
            <label for="modificateur_{{$_mod->code}}{{$_mod->_double}}">
              {{$_mod->code}}
            </label>
          </span>
                    <span>{{$_mod->libelle}}</span>
                    <br/>
                    {{foreachelse}}
                    <em>{{tr}}None{{/tr}}</em>
                {{/foreach}}
            </td>
        </tr>

        <tr>
            <th>{{mb_label object=$acte field=ald}}</th>
            <td>{{mb_field object=$acte field=ald}}</td>
        </tr>

        <!-- Dents -->
        {{if $phase->nb_dents}}
            {{assign var=teeth_checked value=0}}
            {{if is_countable($acte->_dents) && $acte->_dents|@count}}
                {{assign var=teeth_checked value=$acte->_dents|@count}}
            {{/if}}
            <tr>
                <th>Dent(s) concernée(s) (<span id="checked_teeth-{{$view}}">{{$teeth_checked}}</span>
                    / {{$phase->nb_dents}} sélectionnée(s))
                </th>
                <td class="text" colspan="10">
                    {{mb_include module=salleOp template=inc_schema_dents_ccam liste_dents=$liste_dents phase=$phase acte=$acte}}
                </td>
            </tr>
        {{/if}}

        <!-- Remboursable -->
        <tr>
            <th>
                {{if $code->remboursement == 2 || $code->remboursement == 3}}
                    {{assign var=remb value=$code->remboursement}}
                    <i class="fas fa-exclamation-triangle fa-lg" style="color: #ffa30c;"
                       title="{{'Ox\Core\CAppUI::tr'|static_call:"CDatedCodeCCAM-msg-remboursement.$remb":$code->code}}"></i>
                {{/if}}
                {{mb_label object=$acte field=rembourse}}<br/>
                <small><em>({{tr}}CDatedCodeCCAM.remboursement.{{$code->remboursement}}{{/tr}})</em></small>
            </th>
            <td>
                {{assign var=disabled value=""}}
                {{if $code->remboursement == 1}}{{assign var=disabled value=0}}{{/if}}
                {{if $code->remboursement == 2}}{{assign var=disabled value=1}}{{/if}}

                {{assign var=default value=""}}
                {{if $code->remboursement == 1}}{{assign var=default value=1}}{{/if}}
                {{if $code->remboursement == 2}}{{assign var=default value=0}}{{/if}}

                {{mb_field object=$acte field=rembourse disabled=$disabled default=$default}}
            </td>
        </tr>

        <!-- Acte gratuit -->
        <tr>
            <th>
                {{mb_label object=$acte field=gratuit}}
            </th>
            <td>
                {{mb_field object=$acte field=gratuit}}
            </td>
        </tr>

        <!-- Facturable -->
        <tr>
            <th>
                {{mb_label object=$acte field=facturable}}
            </th>
            <td>
                {{if $acte->_tarif_base == 0}}
                    Non
                    <input name="facturable" value="0" hidden="hidden"/>
                {{else}}
                    {{mb_field object=$acte field=facturable}}
                {{/if}}
            </td>
        </tr>

        <tr>
            <th>
                {{if $code->entente_prealable}}
                    <i id="info_dep" class="fa fa-lg fa-exclamation-circle"
                       style="color: #{{if $acte->accord_prealable && $acte->date_demande_accord && $acte->reponse_accord}}197837{{else}}ffa30c{{/if}};"
                       title="{{tr}}CActeCCAM-msg-dep{{/tr}}"></i>
                {{/if}}
                {{mb_label object=$acte field=accord_prealable}}
            </th>
            <td>
                {{mb_field object=$acte field=accord_prealable onchange="toggleDateDAP(this); checkDEP(this.form);"}}
            </td>
        </tr>

        <tbody id="accord_prealable"{{if !$acte->accord_prealable}} style="display: none;"{{/if}}>
        <tr>
            <th>
                {{mb_label object=$acte field=date_demande_accord}}
            </th>
            <td>
                {{mb_field object=$acte field=date_demande_accord form="formEditFullActe-$view" register=true onchange="checkDEP(this.form);"}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$acte field=reponse_accord}}
            </th>
            <td>
                {{mb_field object=$acte field=reponse_accord emptyLabel='Select' onchange="checkDEP(this.form);"}}
            </td>
        </tr>
        </tbody>

        <tr>
            <th>
                {{mb_label object=$acte field=exoneration}}
            </th>
            <td>
                {{mb_field object=$acte field=exoneration style="width: 15em;"}}
            </td>
        </tr>

        <!-- Dépassement d'honoraire -->
        {{if $acte->facturable && $acte->_tarif_base != 0}}
            <tr>
                <th>{{mb_label object=$acte field=montant_depassement}}</th>
                <td>
                    {{mb_field object=$acte field=montant_depassement}}
                    {{mb_field object=$acte field=motif_depassement emptyLabel="CActeCCAM-motif_depassement" style="width: 15em;"}}
                </td>
            </tr>
        {{/if}}

        <!-- Code d'Association -->
        <tr>
            <th>{{mb_label object=$acte field=code_association}}</th>
            <td>
                {{mb_field object=$acte field=code_association emptyLabel="CActeCCAM.code_association." style="width: 15em;"}}
            </td>
        </tr>

        <!-- Commentaires -->
        <tr>
            <th>{{mb_label object=$acte field=commentaire}}</th>
            <td>{{mb_field object=$acte field=commentaire class="autocomplete" form="formEditFullActe-$view"}}</td>
        </tr>

        {{if "oxCabinet"|module_active && $acte->_ref_object|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
            {{mb_include module=cabinet template=inc_fields_prescription with_form=0 form="formEditFullActe-$view"}}
        {{/if}}

        <tr>
            <td class="button" colspan="10">
                <button type="button" class="save" onclick="this.form.onsubmit();">
                    {{tr}}Save{{/tr}}
                </button>
                <button type="button" class="cancel" onclick="window.urlEditActe.modalObject.close();">
                    {{tr}}Cancel{{/tr}}
                </button>
            </td>
        </tr>
    </table>
</form>
