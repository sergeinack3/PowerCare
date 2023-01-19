{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$consult->_ref_patient}}
{{assign var=praticien value=$consult->_ref_chir}}
{{assign var=accident_travail value=$consult->_ref_accident_travail}}
{{mb_default var=opened_factures value=false}}

{{mb_script module="dPcabinet" script="facture" ajax="true"}}
{{mb_script module=cabinet script=cotation ajax=true}}
{{mb_script module=cabinet script=accident_travail ajax=true}}

{{if $consult->_ref_plageconsult->pour_compte_id}}
    {{assign var=pour_compte_praticien_id value=$consult->_ref_plageconsult->pour_compte_id}}
{{else}}
    {{assign var=pour_compte_praticien_id value=$praticien->_id}}
{{/if}}

{{assign var=modAmeli value="ameli"|module_active}}
{{assign var=modFSE value="fse"|module_active}}

{{mb_ternary var=displayFSE test=$consult->sejour_id value=0 other=$modFSE}}

{{mb_default var=only_cotation value=0}}

{{if $only_cotation}}
    {{mb_include module=cabinet template=inc_cotation}}

    {{mb_return}}
{{/if}}

{{* Affichage de la nouvelle vue des FSE *}}
{{if 'oxPyxvital'|module_active && $displayFSE && $app->user_prefs.LogicielFSE == 'oxPyxvital'}}
    {{mb_include module=oxPyxvital template=includes}}
    <script type="text/javascript">
        Main.add(function () {
            window.pyxvital_client.viewFSE('{{$consult->_id}}', 'dPcabinet');
        });
    </script>
{{/if}}

{{if $modAmeli}}
    {{mb_script module=ameli script=AvisArretTravail ajax=true}}
{{/if}}

<script type="text/javascript">
    Main.add(function () {
        {{if $modAmeli}}
        AvisArretTravail.loadArretTravail('{{$consult->_id}}', null, '{{$consult->_class}}');
        {{/if}}
        AccidentTravail.loadAccidentTravail('{{$consult->_id}}', null, '{{$consult->_class}}');
    });
</script>

<table class="layout" style="width: 100%;">
    {{if !$displayFSE || $app->user_prefs.LogicielFSE != 'jfse'}}
        {{assign var=info_colspan value=2}}
        {{if ($past_consults|@count && !$consult->sejour_id) && ($opened_factures && $opened_factures|@count)}}
            {{assign var=info_colspan value=1}}
        {{/if}}
        {{if ($past_consults|@count && !$consult->sejour_id) || ($opened_factures && $opened_factures|@count)}}
            <tr>
                {{if $past_consults|@count && !$consult->sejour_id}}
                    <td colspan="{{$info_colspan}}">
                        {{mb_include module=cabinet template=info_consults_no_regle callback_no_regle="function() {Reglement.reload();}"}}
                    </td>
                {{/if}}
                {{if $opened_factures && $opened_factures|@count}}
                    <td colspan="{{$info_colspan}}">
                        <div class="small-warning">
                            <strong>{{tr}}CFacture.This invoices are opened{{/tr}}</strong>
                            <ul>
                                {{foreach from=$opened_factures item=_opened_facture}}
                                    <li>
                                        <button type="button" class="edit notext"
                                                onclick="Facture.edit('{{$_opened_facture->_id}}}', '{{$_opened_facture->_class}}')">{{tr}}Open{{/tr}}</button>
                                        {{$_opened_facture}}
                                        - {{tr}}CFacture.Opened the{{/tr}} {{mb_value object=$_opened_facture field=ouverture}}
                                    </li>
                                {{/foreach}}
                            </ul>
                        </div>
                    </td>
                {{/if}}
            </tr>
        {{/if}}
        {{if $past_intervs|@count && !$consult->sejour_id}}
            <tr>
                <td colspan="2">
                    <div class="small-info">
                        <strong>{{tr}}COperation.past_dh_no_regle{{/tr}} :</strong>
                        <ul>
                            {{foreach from=$past_intervs item=_interv}}
                                <li>
                                    {{$_interv}}
                                    (
                                    {{foreach from=$_interv->_actes_non_regles item=_acte name=actes}}
                                        {{$_acte->code_acte}}
                                        {{if !$smarty.foreach.actes.last}}, {{/if}}
                                    {{/foreach}}
                                    )
                                </li>
                            {{/foreach}}
                        </ul>
                    </div>
                </td>
            </tr>
        {{/if}}
    {{/if}}
    <tr>
        {{if $displayFSE && $app->user_prefs.LogicielFSE == 'jfse' && "jfse General mode"|gconf === 'hidden'}}
            {{mb_script module=jfse script=Jfse ajax=true}}
            {{mb_script module=jfse script=Invoicing ajax=true}}
            {{mb_script module=jfse script=JfseGui ajax=true}}
            <script type="text/javascript">
                Main.add(function() {
                    Invoicing.reload('{{$consult->_id}}');
                });
            </script>
            <td id="jfse_invoice"></td>
        {{else}}
            <td class="halfPane">
                {{if $modAmeli && $displayFSE && ($app->user_prefs.LogicielFSE == 'pv'|| $app->user_prefs.LogicielFSE == 'oxPyxvital')}}
                    <div id="arret_travail"></div>
                {{/if}}
                <div id="cotation">
                    {{if $view == 'oxCabinet'}}
                        {{mb_include module=cabinet template=inc_patient_medecins patient=$consult->_ref_patient}}
                    {{/if}}
                    {{mb_include module=cabinet template=inc_cotation}}
                    {{if $view == 'oxCabinet' && 'oxPyxvital'|module_active && $displayFSE && $app->user_prefs.LogicielFSE == "oxPyxvital"}}
                        <div id="scor">
                            {{mb_include module=oxPyxvital template=scor/inc_scor}}
                        </div>
                    {{/if}}
                </div>
            </td>
            <td class="halfPane me-valign-top"
                id="{{if $displayFSE && $app->user_prefs.LogicielFSE == "oxPyxvital" && 'oxPyxvital'|module_active}}fse"
                style="height: 450px;{{else}}accident_travail{{/if}}">
                {{* Affichage de la vue FSE *}}
                {{if 'pyxVital'|module_active && $app->user_prefs.LogicielFSE == 'pv'}}
                    <div id="accident_travail"></div>
                    {{mb_include module=fse template=inc_gestion_fse}}
                {{elseif !$displayFSE || ($app->user_prefs.LogicielFSE != 'pv' && $app->user_prefs.LogicielFSE != 'oxPyxvital')}}
                    {{if $modAmeli}}
                        <div id="arret_travail"></div>
                    {{/if}}
                    <div id="accident_travail_mp"></div>
                {{elseif $displayFSE && $app->user_prefs.LogicielFSE == 'jfse' && "jfse General mode"|gconf === 'gui'}}
                    {{mb_script module=jfse script=Jfse ajax=true}}
                    {{mb_script module=jfse script=Invoicing ajax=true}}
                    {{mb_script module=jfse script=JfseGui ajax=true}}

                    <script type="text/javascript">
                        Main.add(function() {
                            JfseGui.reloadInvoice('{{$consult->_id}}');
                        });
                    </script>
                    <div id="jfse_invoice"></div>
                {{/if}}
            </td>
        {{/if}}
    </tr>
    <tr>
        <td id="load_facture" colspan="2" style="padding-top: 10px;">
            {{if $facture->_id || $consult->_ref_facture->_ref_reglements|@count}}
                {{mb_include module=cabinet template="inc_vw_facturation"}}
            {{/if}}
        </td>
    </tr>
    {{if $consult->_ref_factures|@count > 1}}
        <tr>
            <td colspan="2">
                {{foreach from=$consult->_ref_factures item=_facture}}
                    {{if $_facture->numero != 1}}
                        <button type="button" class="search"
                                onclick="Facture.edit('{{$_facture->_id}}', '{{$_facture->_class}}');">{{tr var1=$_facture->numero}}CFactureEtablissement-Bill number %s-court{{/tr}}</button>
                    {{/if}}
                {{/foreach}}
            </td>
        </tr>
    {{/if}}
</table>
