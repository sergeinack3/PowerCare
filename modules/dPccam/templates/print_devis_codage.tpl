{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
    <tr>
        <th class="title" colspan="2">
            {{if $devis->event_type == 'CConsultation'}}
                {{assign var=event_type value='consultation'}}
            {{else}}
                {{assign var=event_type value='intervention'}}
            {{/if}}
            {{tr var1=$event_type}}CDevisCodage-print devis title event type{{/tr}}
            {{mb_value object=$devis field=date}}
        </th>
    </tr>
    <tr>
        <th colspan="2" class="category">
            {{$devis->libelle}}
        </th>
    </tr>
    <tr>
        <td class="halfpane" id="infosPraticien">
            <fieldset>
                {{assign var=praticien value=$devis->_ref_praticien}}
                <legend>{{tr}}common-Practitioner{{/tr}}</legend>
                <table class="tbl">
                    <tr>
                        <td class="me-text-align-left">{{tr}}common-Name{{/tr}} :</td>
                        <td class="me-text-align-left">
                            {{mb_value object=$praticien field=_user_last_name}} {{mb_value object=$praticien field=_user_first_name}}
                        </td>
                    </tr>
                    {{if $praticien->adeli || $praticien->rpps}}
                        {{mb_ternary var=idnat test=$praticien->adeli value=$praticien->adeli other=$praticien->rpps}}
                        <tr>
                            <td class="me-text-align-left">N° {{tr}}common-ID{{/tr}} :</td>
                            <td class="me-text-align-left">{{$idnat}}</td>
                        </tr>
                    {{/if}}
                    {{if $praticien->_ref_function}}
                        {{assign var=function value=$praticien->_ref_function}}
                        {{if $function->adresse && $function->ville && $function->cp}}
                            <tr>
                                <td class="me-text-align-left">Adresse :</td>
                                <td class="me-text-align-left">
                                    {{$function->adresse}} {{$function->cp}} {{$function->ville}}
                                </td>
                            </tr>
                        {{/if}}
                        {{if $function->tel}}
                            <tr>
                                <td class="me-text-align-left">{{tr}}common-Phone{{/tr}} :</td>
                                <td class="me-text-align-left">{{$function->tel}}</td>
                            </tr>
                        {{/if}}
                    {{/if}}
                </table>
            </fieldset>
        </td>
        <td class="halfPane" id="infosPatient">
            <fieldset>
                {{assign var=patient value=$devis->_ref_patient}}
                <legend>Patient</legend>
                <table class="tbl">
                    <tr>
                        <td class="me-text-align-left">{{tr}}common-Name{{/tr}} :</td>
                        <td class="me-text-align-left">
                            {{mb_value object=$patient field=nom}} {{mb_value object=$patient field=prenom}}
                        </td>
                    </tr>
                    <tr>
                        <td class="me-text-align-left">{{tr}}common-Address{{/tr}} :</td>
                        <td class="me-text-align-left">{{$patient->adresse}} {{$patient->cp}} {{$patient->ville}}</td>
                    </tr>
                    <tr>
                        <td class="me-text-align-left">{{tr}}common-Phone{{/tr}} :</td>
                        <td class="me-text-align-left">{{$patient->tel}}</td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td colspan="2" id="infosActes">
            <table class="tbl">
                {{if $devis->_ref_actes_ccam|@count != 0}}
                    <tr>
                        <th colspan="8" class="title">{{tr}}CActeCCAM{{/tr}}</th>
                    </tr>
                    <tr>
                        <th class="narrow"></th>
                        <th class="narrow">{{tr}}CActeCCAM-code_acte-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeCCAM-code_activite-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeCCAM-modificateurs-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeCCAM-montant_base-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeCCAM-montant_depassement-court{{/tr}}</th>
                        <th class="narrow"></th>
                        <th class="narrow">{{tr}}common-Total{{/tr}}</th>
                    </tr>
                    {{foreach from=$devis->_ref_actes_ccam item=_acte}}
                        <tr>
                            <td class="me-text-align-center"></td>
                            <td class="me-text-align-center">{{mb_value object=$_acte field=code_acte}}</td>
                            <td class="me-text-align-center">{{mb_value object=$_acte field=code_activite}}
                                - {{mb_value object=$_acte field=code_phase}}</td>
                            <td class="me-text-align-center">
                                {{mb_value object=$_acte field=modificateurs}}
                                {{if $_acte->position_dentaire}}
                                    {{tr}}Tooth{{/tr}} n° {{$_acte->position_dentaire|replace:'|':', '}}
                                {{/if}}
                            </td>
                            <td class="me-text-align-right">{{mb_value object=$_acte field=montant_base}}</td>
                            <td class="me-text-align-right">{{mb_value object=$_acte field=montant_depassement}}</td>
                            <td class="me-text-align-right">-</td>
                            <td class="me-text-align-right">{{mb_value object=$_acte field=_total}}</td>
                        </tr>
                    {{/foreach}}
                {{/if}}
                {{if $devis->_ref_actes_ngap|@count != 0}}
                    <tr>
                        <th colspan="8" class="title">{{tr}}CActeNGAP|pl{{/tr}}</th>
                    </tr>
                    <tr>
                        <th class="narrow">{{tr}}CActeNGAP-quantite-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeNGAP-code-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeNGAP-libelle-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeNGAP-coefficient-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeNGAP-montant_base-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeNGAP-montant_depassement-court{{/tr}}</th>
                        <th class="narrow"></th>
                        <th class="narrow">{{tr}}common-Total{{/tr}}</th>
                    </tr>
                    {{foreach from=$devis->_ref_actes_ngap item=_acte}}
                        <tr>
                            <td class="me-text-align-center">{{mb_value object=$_acte field=quantite}}</td>
                            <td class="me-text-align-center">{{mb_value object=$_acte field=code}}</td>
                            <td class="me-text-align-center">
                                {{$_acte->_libelle}}
                                {{if $_acte->comment_acte}}
                                    - {{mb_value object=$_acte field=comment_acte}}
                                {{/if}}
                            </td>
                            <td class="me-text-align-center">{{mb_value object=$_acte field=coefficient}}</td>
                            <td class="me-text-align-right">{{mb_value object=$_acte field=montant_base}}</td>
                            <td class="me-text-align-right">{{mb_value object=$_acte field=montant_depassement}}</td>
                            <td class="me-text-align-right">-</td>
                            <td class="me-text-align-right">
                                {{mb_value object=$_acte field=_tarif}}
                            </td>
                        </tr>
                    {{/foreach}}
                {{/if}}

                {{if $devis->_ref_actes_lpp|@count != 0}}
                    <tr>
                        <th colspan="8" class="title">{{tr}}CActeLPP|pl{{/tr}}</th>
                    </tr>
                    <tr>
                        <th class="narrow">{{tr}}CActeLPP-quantite-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeLPP-code-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeLPP-code_prestation-court{{/tr}}</th>
                        <th class="narrow"></th>
                        <th class="narrow">{{tr}}CActeLPP-montant_final-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CActeLPP-montant_depassement-court{{/tr}}</th>
                        <th class="narrow"></th>
                        <th class="narrow">{{tr}}common-Total{{/tr}}</th>
                    </tr>
                    {{foreach from=$devis->_ref_actes_lpp item=_acte}}
                        <tr>
                            <td class="me-text-align-center">
                                {{mb_value object=$_acte field=quantite}}
                            </td>
                            <td class="me-text-align-center">
                                {{mb_value object=$_acte field=code}}
                            </td>
                            <td class="me-text-align-center">
                                {{mb_value object=$_acte field=code_prestation}}
                            </td>
                            <td class="me-text-align-center"></td>
                            <td class="me-text-align-right">
                                {{mb_value object=$_acte field=montant_final}}
                            </td>
                            <td class="me-text-align-right">
                                {{mb_value object=$_acte field=montant_depassement}}
                            </td>
                            <td class="me-text-align-right">-</td>
                            <td class="me-text-align-right">
                                {{mb_value object=$_acte field=montant_total}}
                            </td>
                        </tr>
                    {{/foreach}}
                {{/if}}

                {{if $devis->_ref_frais_divers|@count != 0}}
                    <tr>
                        <th colspan="7" class="title">Frais divers</th>
                    </tr>
                    <tr>
                        <th class="narrow">{{tr}}CFraisDivers-quantite-court{{/tr}}</th>
                        <th class="narrow"></th>
                        <th class="narrow">{{tr}}CFraisDiversType-libelle-court{{/tr}}</th>
                        <th class="narrow">{{tr}}CFraisDivers-coefficient-court{{/tr}}</th>
                        <th class="narrow"></th>
                        <th class="narrow"></th>
                        <th class="narrow">{{tr}}common-HT{{/tr}}</th>
                        <th class="narrow">{{tr}}common-Total{{/tr}}</th>
                    </tr>
                    {{foreach from=$devis->_ref_frais_divers item=_frais}}
                        <tr>
                            <td class="me-text-align-center">{{mb_value object=$_frais field=quantite}}</td>
                            <td class="me-text-align-center"></td>
                            <td class="me-text-align-center">{{mb_value object=$_frais->_ref_type field=libelle}}</td>
                            <td class="me-text-align-center">{{mb_value object=$_frais field=coefficient}}</td>
                            <td class="me-text-align-right">-</td>
                            <td class="me-text-align-right">-</td>
                            <td class="me-text-align-right">{{mb_value object=$_frais field=montant_base}}</td>
                            <td class="me-text-align-right">{{mb_value object=$_frais field=montant_base}}</td>
                        </tr>
                    {{/foreach}}
                {{/if}}
              <tr>
                <th colspan="8" class="title">{{tr}}total-codage-actes{{/tr}}</th>
              </tr>
              <tr>
                    <th colspan="4" class="narrow"></th>
                    <th class="narrow">{{mb_label object=$devis field=base}}</th>
                    <th class="narrow">{{mb_label object=$devis field=dh}}</th>
                    <th class="narrow">{{mb_label object=$devis field=ht}}</th>
                    <th class="narrow">{{mb_label object=$devis field=_total}}</th>
                </tr>
                <tr>
                    <td colspan="5" class="me-text-align-right">{{mb_value object=$devis field=base}}</td>
                    <td class="me-text-align-right">{{mb_value object=$devis field=dh}}</td>
                    <td class="me-text-align-right">{{mb_value object=$devis field=ht}}</td>
                    <td class="me-text-align-right">{{mb_value object=$devis field=_total}}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
