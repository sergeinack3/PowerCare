{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !"dPccam codage use_cotation_ccam"|gconf}}
    {{mb_return}}
{{/if}}
{{if !isset($date|smarty:nodefaults)}}
    {{assign var=date value=$dnow}}
{{/if}}

<script>
    Main.add(function () {
        Calendar.regField(getForm('facture_date-' + '{{$facture->_guid}}').ouverture);
    });
</script>
<tr>
    <td style="text-align: center;"><b>{{mb_label object=$facture field=ouverture}}</b></td>
    <td>
        <form name="facture_date-{{$facture->_guid}}" method="post" action=""
              onsubmit="return Facture.editDateFacture(this);">
            {{mb_key object=$facture}}
            {{mb_class object=$facture}}
            <input type="hidden" name="cloture" value=""/>
            {{mb_field object=$facture field=ouverture class="date notNull" onchange="this.form.onsubmit();"}}
        </form>
    </td>
    <td colspan="4" class="me-text-align-right">
        {{if $facture->_is_relancable && "dPfacturation CRelance use_relances"|gconf}}
            <form name="facture_relance" method="post" action="" onsubmit="return Relance.create(this);">
                {{mb_class object=$facture->_ref_last_relance}}
                <input type="hidden" name="relance_id" value=""/>
                <input type="hidden" name="object_id" value="{{$facture->_id}}"/>
                <input type="hidden" name="object_class" value="{{$facture->_class}}"/>
                <button class="add" type="button" onclick="this.form.onsubmit();">
                    {{tr}}CFacture-action-create-relance{{/tr}}
                </button>
            </form>
        {{/if}}
        {{*if !$facture->annule && $facture->_reglements_total == 0 && !$facture->_ref_echeances|@count}}
            <form name="facture_annule" method="post" action="?">
                {{mb_class object=$facture}}
                {{mb_key   object=$facture}}
                <input type="hidden" name="facture_class" value="{{$facture->_class}}"/>
                <input type="hidden" name="_duplicate" value="0"/>
                <input type="hidden" name="annule" value="1"/>
                <button type="button" class="cancel" onclick="Facture.annule(this.form)">
                    {{tr}}Cancel{{/tr}}
                </button>
            </form>
        {{/if*}}
        {{if $facture->cloture}}
                <button type="button" class="cancel notext" onclick="Facture.annule(this.form)">
                    {{tr}}Cancel{{/tr}}
                </button>
            <button type="button" class="pdf notext"
                    onclick="Facture.printFactureFR('{{$facture->_id}}', '{{$facture->_class}}');">
                {{tr}}CFacture-action-pdf-invoice{{/tr}}
            </button>
            <button type="button" class="mail notext"
                    onclick="Facture.sendFactureByMail('{{$facture->_id}}', '{{$facture->_class}}');">
                {{tr}}CFacture-action-pdf-send{{/tr}}
            </button>
        {{/if}}
    </td>
</tr>

<tr>
    <th class="category narrow">{{tr}}CFacture-date{{/tr}}</th>
    <th class="category">{{tr}}CFacture-code{{/tr}}</th>
    <th class="category">{{tr}}CFacture-libelle{{/tr}}</th>
    <th class="category narrow">{{tr}}CFacture-base{{/tr}}</th>
    <th class="category narrow">{{tr}}CFacture-dh{{/tr}}</th>
    <th class="category narrow">{{tr}}CFacture-montant{{/tr}}</th>
</tr>

{{if $facture->_ref_items|@count}}
    {{foreach from=$facture->_ref_items item=item}}
        <tr>
            <td style="text-align:center;width:100px;">
                {{if $facture->_ref_last_sejour->_id}}
                <span onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_last_sejour->_guid}}')">
        {{else}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_last_consult->_guid}}')">
        {{/if}}
              {{mb_value object=$item field="date"}}
        </span>
            </td>
            <td class="acte-{{$item->type}}" style="width:140px;">{{mb_value object=$item field="code"}}</td>
            <td style="white-space: pre-line;" class="compact">{{mb_value object=$item field="libelle"}}</td>
            <td style="text-align:right;">{{mb_value object=$item field="montant_base"}}</td>
            <td style="text-align:right;">{{mb_value object=$item field="montant_depassement"}}</td>
            {{math equation="x+y" x=$item->montant_base y=$item->montant_depassement assign=_montant_facture}}
            <td style="text-align:right;">{{$_montant_facture|string_format:"%0.2f"|currency}}</td>
        </tr>
    {{/foreach}}
{{else}}
    {{foreach from=$facture->_ref_actes_ccam item=_acte_ccam}}
        <tr>
            <td>{{$_acte_ccam->execution|date_format:$conf.date}}</td>
            <td class="acte-{{$_acte_ccam->_class}}">{{$_acte_ccam->code_acte}}</td>
            <td>{{$_acte_ccam->_ref_code_ccam->libelleLong|truncate:70:"...":true}}</td>
            <td style="text-align: right;">{{mb_value object=$_acte_ccam field=montant_base}}</td>
            <td style="text-align: right;">{{mb_value object=$_acte_ccam field=montant_depassement}}</td>
            <td style="text-align: right;">{{mb_value object=$_acte_ccam field=_montant_facture}}</td>
        </tr>
    {{/foreach}}

    {{foreach from=$facture->_ref_actes_ngap item=_acte_ngap}}
        <tr>
            <td>{{$_acte_ngap->execution|date_format:$conf.date}}</td>
            <td class="acte-{{$_acte_ngap->_class}}">{{$_acte_ngap->code}}</td>
            <td>
                {{if $_acte_ngap->comment_acte}}
                    {{$_acte_ngap->_libelle}} - {{$_acte_ngap->comment_acte}}
                {{else}}
                    {{$_acte_ngap->_libelle}}
                {{/if}}
            </td>
            <td style="text-align: right;">{{mb_value object=$_acte_ngap field="montant_base"}}</td>
            <td style="text-align: right;">{{mb_value object=$_acte_ngap field="montant_depassement"}}</td>
            <td style="text-align: right;">{{mb_value object=$_acte_ngap field=_montant_facture}}</td>
        </tr>
    {{/foreach}}
    {{foreach from=$facture->_ref_actes_divers item=_acte_divers}}
        <tr>
            <td>{{mb_value object=$_acte_divers field=execution format=$conf.date}}</td>
            <td class="acte-{{$_acte_divers->_class}}">{{$_acte_divers->_ref_type->code}}</td>
            <td>{{$_acte_divers->_ref_type->libelle}}</td>
            <td style="text-align: right;">{{mb_value object=$_acte_divers field="montant_base"}}</td>
            <td style="text-align: right;">{{mb_value object=$_acte_divers field="montant_depassement"}}</td>
            <td style="text-align: right;">{{mb_value object=$_acte_divers field=_montant_facture}}</td>
        </tr>
    {{/foreach}}
{{/if}}

<tbody class="hoverable">
<tr>
    <td colspan="3" rowspan="4"></td>
    <td colspan="2">{{mb_label object=$facture field="du_patient"}}</td>
    <td style="text-align:right;">{{mb_value object=$facture field="du_patient"}}</td>
</tr>
<tr>
    <td colspan="2">{{mb_label object=$facture field="du_tiers"}}</td>
    <td style="text-align:right;">{{mb_value object=$facture field="du_tiers"}}</td>
</tr>
<tr>
    <td colspan="2"><i>{{tr}}CFacture-whose_tva{{/tr}} ({{$facture->taux_tva}}%)</i></td>
    <td style="text-align:right;"><i>{{mb_value object=$facture field="du_tva"}}</i></td>
<tr>
<tr>
    <td colspan="3">
        {{if $facture->numero == 1 && $facture->cloture && !$facture->_is_urg}}
            <button class="edit notext" style="float:right;"
                    onclick="Facture.editRepartition('{{$facture->_id}}', '{{$facture->_class}}');">{{tr}}CFactureCabinet-action-modify-du-patient-tiers{{/tr}}</button>
        {{/if}}
    </td>
    <td colspan="2"><b>{{mb_label object=$facture field="_montant_avec_remise"}}</b></td>
    <td style="text-align:right;"><b>{{mb_value object=$facture field="_montant_avec_remise"}}</b></td>
<tr>
    {{assign var="classe" value=$facture->_class}}
    {{if !$facture->_reglements_total_patient && !"dPfacturation $classe use_auto_cloture"|gconf && !$facture->annule &&
    !$facture->definitive && !$facture->_ref_echeances|@count}}
<tr>
    <td colspan="7">
        <form name="change_type_facture" method="post">
            {{mb_class object=$facture}}
            {{mb_key   object=$facture}}
            <input type="hidden" name="facture_class" value="{{$facture->_class}}"/>
            <input type="hidden" name="cloture" value="{{if !$facture->cloture}}{{$date}}{{/if}}"/>
            <input type="hidden" name="not_load_banque"
                   value="{{if isset($factures|smarty:nodefaults) && count($factures)}}0{{else}}1{{/if}}"/>
            {{if !$facture->cloture}}
                <button class="submit" type="button"
                        onclick="Facture.modifCloture(this.form);">{{tr}}CFactureCabinet-action-close-invoice{{/tr}}</button>
            {{else}}
                <button class="submit" type="button"
                        onclick="Facture.modifCloture(this.form);">{{tr}}CFactureCabinet-action-reopen-invoice{{/tr}}</button>
                Cloturée le {{$facture->cloture|date_format:$conf.date}}
            {{/if}}
        </form>
    </td>
</tr>
{{/if}}
</tbody>
