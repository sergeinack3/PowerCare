{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_ternary var=view test=$devis->_id value=$devis->_id other="new"}}

{{if $devis->date}}
    <script type="text/javascript">
        Main.add(function () {
            var tabsActes = Control.Tabs.create('tab-actes-devis', true);
        });
    </script>
{{/if}}

<table class="form">
    <tr>
        <th colspan="2" class="title">
            {{tr}}CDevisCodage{{/tr}} pour {{$devis->_ref_patient}}
            {{mb_include module=system template=inc_object_history object=$devis}}
        </th>
    </tr>
    <tr>
        <td class="halfPane">
            <fieldset>
                <legend>Informations sur le devis</legend>
                <form name="editDevis-{{$view}}" action="?" method="post"
                      onsubmit="return onSubmitFormAjax(this, {onComplete: function() {
                        DevisCodage.refresh('{{$devis->_id}}');
                        }});">
                    {{mb_class object=$devis}}
                    {{mb_key object=$devis}}
                    <input type="hidden" name="del" value="0"/>

                    {{mb_field object=$devis field=codable_class hidden=true}}
                    {{mb_field object=$devis field=codable_id hidden=true}}
                    {{mb_field object=$devis field=patient_id hidden=true}}
                    {{mb_field object=$devis field=praticien_id hidden=true}}
                    {{mb_field object=$devis field=creation_date hidden=true}}
                    {{mb_field object=$devis field=base hidden=true}}
                    {{mb_field object=$devis field=dh hidden=true}}
                    {{mb_field object=$devis field=ht hidden=true}}
                    {{mb_field object=$devis field=tax_rate hidden=true}}
                    {{mb_field object=$devis field=_generate_pdf hidden=true value=1}}

                    <table class="layout main">
                        <tr>
                            <th>
                                {{mb_label object=$devis field=date class=notNull}}
                            </th>
                            <td>
                                {{mb_field object=$devis field=date form="editDevis-$view" class=notNull register=true}}
                            </td>
                            <th>
                                {{mb_label object=$devis field=libelle class=notNull}}
                            </th>
                            <td>
                                {{mb_field object=$devis field=libelle class=notNull}}
                            </td>
                        </tr>
                        <tr>
                            <th>
                                {{mb_label object=$devis field=event_type class=notNull}}
                            </th>
                            <td>
                                {{mb_field object=$devis field=event_type class=notNull}}
                            </td>
                            <th>
                                {{mb_label object=$devis field=patient_id}}
                            </th>
                            <td>
                                {{$devis->_ref_patient}}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                {{mb_label object=$devis field=comment}}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                {{mb_field object=$devis field=comment}}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" class="button">
                                <button class="save" type="submit">{{tr}}Save{{/tr}}</button>
                                <button class="print" type="button"
                                        onclick="DevisCodage.print('{{$devis->_id}}')">{{tr}}Print{{/tr}}</button>
                                <button class="trash" type="button" onclick="$V(this.form.del, 1); return onSubmitFormAjax(this.form, {onComplete: function() {
                  this.form, window.parent.Control.Modal.close();}});">
                                    {{tr}}Delete{{/tr}}
                                </button>
                            </td>
                        </tr>
                    </table>
                </form>
            </fieldset>
        </td>
        <td>
            <fieldset>
                <legend>{{tr}}CConsultation-cotation{{/tr}}</legend>
                <form name="selection_tarif" action="?" method="post"
                      onsubmit="return onSubmitFormAjax(this, DevisCodage.refresh.curry('{{$devis->_id}}'))">
                    <input type="hidden" name="m" value="ccam"/>
                    <input type="hidden" name="del" value="0"/>
                    <input type="hidden" name="_bind_tarif" value="1"/>
                    {{mb_key object=$devis}}
                    {{mb_class object=$devis}}
                    <table class="layout main">
                        <tr>
                            <th>
                                {{tr}}CConsultation-cotation{{/tr}}
                            </th>
                            <td>
                                <select name="_tarif_id" class="notNull str" style="width: 130px;"
                                        onchange="this.form.onsubmit();">
                                    <option value="" selected="selected">&mdash; {{tr}}Choose{{/tr}}</option>
                                    {{if $tarifs.user|@count}}
                                        <optgroup label="{{tr}}CConsultation-Practitioner price{{/tr}}">
                                            {{foreach from=$tarifs.user item=_tarif}}
                                                <option value="{{$_tarif->_id}}"
                                                        {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                                            {{/foreach}}
                                        </optgroup>
                                    {{/if}}
                                    {{if $tarifs.func|@count}}
                                        <optgroup label="{{tr}}CConsultation-Office price{{/tr}}">
                                            {{foreach from=$tarifs.func item=_tarif}}
                                                <option value="{{$_tarif->_id}}"
                                                        {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                                            {{/foreach}}
                                        </optgroup>
                                    {{/if}}
                                    {{if "dPcabinet Tarifs show_tarifs_etab"|gconf && $tarifs.group|@count}}
                                        <optgroup label="{{tr}}CConsultation-Etablishment price{{/tr}}">
                                            {{foreach from=$tarifs.group item=_tarif}}
                                                <option value="{{$_tarif->_id}}"
                                                        {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
                                            {{/foreach}}
                                        </optgroup>
                                    {{/if}}
                                </select>
                            </td>
                        </tr>
                    </table>
                </form>
            </fieldset>
            <fieldset>
                <legend>{{tr}}CDevisCodage-Summary{{/tr}}</legend>
                <form name="editDevisRecap" action="?" method="post" onsubmit="return false;">
                    <table class="layout main">
                        <tr>
                            <th>
                                {{mb_label object=$devis field=base}}
                            </th>
                            <td>
                                {{mb_field object=$devis field=base readonly=readonly}}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th>
                                {{mb_label object=$devis field=dh}}
                            </th>
                            <td>
                                {{mb_field object=$devis field=dh readonly=readonly}}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <th>
                                {{mb_label object=$devis field=ht}}
                            </th>
                            <td>
                                {{mb_field object=$devis field=ht readonly=readonly}}
                            </td>
                            <th>
                                {{mb_label object=$devis field=tax_rate}}
                            </th>
                            <td>
                                {{assign var=default_taux_tva value="dPcabinet CConsultation default_taux_tva"|gconf}}
                                {{assign var=taux_tva value="|"|explode:$default_taux_tva}}
                                <select name="tax_rate" onchange="DevisCodage.syncField(this, '{{$view}}');">
                                    {{foreach from=$taux_tva item=taux}}
                                        <option value="{{$taux}}"
                                                {{if $devis->tax_rate == $taux}}selected="selected"{{/if}}>{{tr}}CConsultation.taux_tva.{{$taux}}{{/tr}}</option>
                                    {{/foreach}}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <strong>
                                    {{mb_label object=$devis field=_total}}
                                </strong>
                            </th>
                            <td>
                                {{mb_field object=$devis field=_total readonly=readonly}}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </table>
                </form>
            </fieldset>
        </td>
    </tr>
    <tr>
    <tr>
        <td colspan="2">
            <ul id="tab-actes-devis" class="control_tabs">
                {{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
                    <li>
                        <a href="#ccam" {{if $devis->_ref_actes_ccam|@count ==0}} class="empty"{{/if}}>
                            Actes CCAM
                            <small>({{$devis->_ref_actes_ccam|@count}})</small>
                        </a>
                    </li>
                    <li>
                        <a href="#ngap" {{if $devis->_ref_actes_ngap|@count ==0}} class="empty"{{/if}}>
                            Actes NGAP
                            <small>({{$devis->_ref_actes_ngap|@count}})</small>
                        </a>
                    </li>
                {{/if}}
                {{if 'lpp'|module_active && "lpp General cotation_lpp"|gconf}}
                    <li>
                        <a href="#lpp" {{if $devis->_ref_actes_lpp|@count ==0}} class="empty"{{/if}}>
                            {{tr}}CActeLPP|pl{{/tr}}
                            <small>({{$devis->_ref_actes_lpp|@count}})</small>
                        </a>
                    </li>
                {{/if}}
                {{if "dPccam frais_divers use_frais_divers_CConsultation"|gconf && "dPccam codage use_cotation_ccam"|gconf}}
                    <li><a href="#fraisdivers">Frais divers</a></li>
                {{/if}}
            </ul>

            <div id="ccam" style="display: none;">
                {{assign var=chir_id        value=$devis->praticien_id}}
                {{assign var=do_subject_aed value=''}}
                {{assign var=module         value='ccam'}}
                {{assign var=object         value=$devis}}
                {{mb_include module=salleOp template=js_codage_ccam}}
                {{assign var=module value="dPcabinet"}}
                {{assign var=subject value=$devis}}
                {{assign var=do_subject_aed value=''}}
                {{mb_include module=salleOp template=inc_codage_ccam}}
            </div>

            <div id="ngap" style="display: none;">
                <div id="listActesNGAP" data-object_id="{{$devis->_id}}" data-object_class="{{$devis->_class}}">
                    {{assign var="_object_class" value="CDevisCodage"}}
                    {{mb_include module=cabinet template=inc_codage_ngap object=$devis}}
                </div>
            </div>


            {{if "dPccam frais_divers use_frais_divers_CConsultation"|gconf && "dPccam codage use_cotation_ccam"|gconf}}
                <div id="fraisdivers" style="display: none;">
                    {{mb_include module=ccam template=inc_frais_divers object=$devis}}
                </div>
            {{/if}}

            {{if 'lpp'|module_active && "lpp General cotation_lpp"|gconf}}
                <div id="lpp" style="display: none;">
                    {{mb_include module=lpp template=inc_codage_lpp codable=$devis}}
                </div>
            {{/if}}
        </td>
    </tr>
</table>
