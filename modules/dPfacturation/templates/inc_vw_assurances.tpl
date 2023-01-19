{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$facture->_ref_patient}}
<form name="assurances-patient" method="post" action="">
  {{mb_class object=$facture}}
  {{mb_key   object=$facture}}
  <input type="hidden" name="facture_guid" value="{{$facture->_guid}}"/>
  <table class="main tbl">
    {{if ($facture->type_facture == "maladie" && $facture->assurance_maladie && !$facture->_ref_assurance_maladie->type_pec) ||
    ($facture->type_facture == "accident" && $facture->assurance_accident && !$facture->_ref_assurance_accident->type_pec)}}
      <tr>
        <td colspan="2">
          <div class="small-warning" style="margin:0;">{{tr}}Facture.type_pec.error{{/tr}}</div>
        </td>
      </tr>
    {{elseif ($facture->type_facture == "maladie" && !$facture->assurance_maladie)
    || ($facture->type_facture == "accident" && !$facture->assurance_accident)}}
      <tr>
        <td colspan="2">
          <div class="small-warning" style="margin:0;">{{tr}}Facture.no_assurance{{/tr}}</div>
        </td>
      </tr>
    {{/if}}
    <tr>
      <td style="text-align:right;">
      <button style="" type="button" class="add notext"
              onclick="Correspondant.edit(0, '{{$patient->_id}}', function(){Facture.refreshAssurance('{{$facture->_guid}}')});">
      </button>
      {{if $facture->_class == "CFactureCabinet" || !$facture->dialyse}}
        {{assign var="type_assur" value=assurance_maladie}}
        {{if $facture->type_facture == "accident"}}
          {{assign var="type_assur" value=assurance_accident}}
        {{/if}}
        {{mb_label object=$facture field=$type_assur}}</td>
        {{mb_include module=facturation template="inc_vw_assurances_patient" object=$facture name=$type_assur}}
      {{else}}
        {{assign var="first_assur"   value=assurance_maladie}}
        {{assign var="seconde_assur" value=assurance_accident}}
        {{if $facture->type_facture == "accident"}}
          {{assign var="first_assur"    value=assurance_accident}}
          {{assign var="seconde_assur"  value=assurance_maladie}}
        {{/if}}
        {{tr}}CFacture-assurance_maladie{{/tr}}</td>
        {{mb_include module=facturation template="inc_vw_assurances_patient" object=$facture name=$first_assur}}
        <td style="text-align:right;">{{tr}}CFacture-assurance_accident{{/tr}}</td>
        {{mb_include module=facturation template="inc_vw_assurances_patient" object=$facture name=$seconde_assur}}
      {{/if}}
    </tr>
    <tr>
      {{if $facture->_class == "CFactureCabinet" || !$facture->dialyse}}
        {{assign var="type_assur" value=send_assur_base}}
        {{if $facture->type_facture == "accident"}}
          {{assign var="type_assur" value=send_assur_compl}}
        {{/if}}
        <td style="text-align:right;">{{mb_label object=$facture field=$type_assur}}</td>
        <td>
          {{mb_field object=$facture field=$type_assur onchange="Facture.saveAssurance(this.form);" readonly=$facture->cloture}}</td>
      {{else}}
        {{assign var="first_assur"   value=send_assur_base}}
        {{assign var="seconde_assur" value=send_assur_compl}}
      
        {{if $facture->type_facture == "accident"}}
          {{assign var="first_assur"    value=send_assur_compl}}
          {{assign var="seconde_assur"  value=send_assur_base}}
        {{/if}}
          <td style="text-align:right;">{{mb_label object=$facture field=$first_assur}}</td>
          <td>
            {{mb_field object=$facture field=$first_assur onchange="Facture.saveAssurance(this.form);" readonly=$facture->cloture}}
          </td>
          <td style="text-align:right;">{{mb_label object=$facture field=$seconde_assur}}</td>
          <td>
            {{mb_field object=$facture field=$seconde_assur onchange="Facture.saveAssurance(this.form);" readonly=$facture->cloture}}
          </td>
      {{/if}}
    </tr>
  </table>
</form>