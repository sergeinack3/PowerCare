{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=use_fact_etab value=0}}
{{if "dPfacturation"|module_active && "dPplanningOp CFactureEtablissement use_facture_etab"|gconf}}
  {{mb_script module=facturation script=rapport ajax=true}}
  {{mb_script module=cabinet script=reglement ajax=true}}
  {{assign var=use_fact_etab value=1}}
{{/if}}
<script>
function submitActeCCAM(oForm, acte_ccam_id, sField){
  if(oForm[sField].value == 1) {
    $V(oForm[sField], 0);
  } else {
    $V(oForm[sField], 1);
  }
  $(sField + '-' + acte_ccam_id).toggleClassName('cancel').toggleClassName('tick');
  return onSubmitFormAjax(oForm, {onComplete: function() { reloadActeCCAM(acte_ccam_id) } });
}

function reloadActeCCAM(acte_ccam_id) {
  var url = new Url;
  url.setModuleAction("dPplanningOp", "httpreq_vw_reglement_ccam");
  url.addParam("acte_ccam_id", acte_ccam_id);
  url.requestUpdate('divreglement-'+acte_ccam_id);
}

function viewCCAM(codeacte) {
  var url = new Url;
  url.setModuleAction("dPccam", "viewCcamCode");
  url.addParam("_codes_ccam", codeacte);
  url.popup(800, 600, "Code CCAM");
}
</script>

<table class="main me-w100">
  <tr>
    <th colspan="2">
      <a href="#" onclick="window.print()">
        Rapport des actes codés
      </a>
    </th>
  </tr>
  <tr>
    <td>
      <table class="main">
        {{if $bloc->_id}}
          <tr>
            <td><strong>{{tr}}CBlocOperatoire{{/tr}}: {{$bloc}}</strong></td>
          </tr>
        {{/if}}
        <tr>
          <td>
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$praticien}}
          </td>
        </tr>
        <tr>
          <td>du {{$_date_min|date_format:$conf.longdate}}</td>
        </tr>
        <tr>
          <td>au {{$_date_max|date_format:$conf.longdate}}</td>
        </tr>
      </table>
    </td>
    <td style="width: 50%;">
      <table class="main tbl" style="float: right;">
        <tr>
          <th>Nombre de séjours</th>
          <td style="text-align: center;">{{$nbActes|@count}}</td>
        </tr>
        <tr>
          <th>Nombre d'actes</th>
          <td style="text-align: center;">{{$totalActes}}</td>
        </tr>
        <tr>
          <th>Total Base</th>
          <td style="text-align: right;">{{$montantTotalActes.base|currency}}</td>
        </tr>
        <tr>
          <th>Total DH</th>
          <td style="text-align: right;">{{$montantTotalActes.dh|currency}}</td>
        </tr>
        <tr>
          <th>Total</th>
          <td style="text-align: right;">{{$montantTotalActes.total|currency}}</td>
        </tr>
      </table>
    </td>
  </tr>

  {{if $typeVue == 1}}
    {{foreach from=$sejours key="key" item="jour"}}
      <tr>
        <td colspan="2">
          <table>
            <tr>
              <td>
                <strong>
                  {{assign var=date value=$key|date_format:$conf.longdate}}
                  {{if $order == 'sortie_reelle'}}
                    Sortie réelle le {{$date}}
                  {{else}}
                    {{$date|ucfirst}}
                  {{/if}}
                </strong>
              </td>
            </tr>
          </table>
          <table class="tbl">
            <tr>
              <th style="width: 20%">{{mb_title class=CFactureEtablissement field=patient_id}}</th>
              <th style="width: 05%">Total Séjour</th>
              <th style="width: 20%">{{mb_label class=CSejour field=type}}</th>
              <th style="width: 20%">{{mb_title class=CActeCCAM field=object_class}}</th>
              <th style="width: 05%">{{mb_title class=CActeCCAM field=code_acte}}</th>
              <th style="width: 05%">Act.</th>
              <th style="width: 05%">{{mb_label class=CActeCCAM field=code_phase}}</th>
              <th style="width: 05%">Mod</th>
              <th style="width: 05%">ANP</th>
              <th style="width: 05%">{{mb_title class=CActeCCAM field=montant_base}}</th>
              <th style="width: 05%">{{mb_title class=CActeCCAM field=montant_depassement}}</th>
              <th style="width: 05%">{{mb_title class=CActeCCAM field=_montant_facture}}</th>
              {{if $use_fact_etab}}
                <th style="width: 05%">Dû établissement</th>
              {{/if}}
            </tr>

            <!-- Parcours des sejours -->
            {{foreach from=$jour item="sejour"}}
            {{assign var="sejour_id" value=$sejour->_id}}
            {{assign var=facture value=$sejour->_ref_facture}}
            <tbody class="hoverable" {{if $facture && $facture->_id}}id="line_{{$facture->_guid}}"{{/if}}>
              <tr>
                <td rowspan="{{$nbActes.$sejour_id}}">
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_ref_patient->_guid}}')">
                  {{$sejour->_ref_patient->_view}} {{if $sejour->_ref_patient->_age}}({{$sejour->_ref_patient->_age}}){{/if}}
                </span>
                </td>
                <td rowspan="{{$nbActes.$sejour_id}}">
                  {{$montantSejour.$sejour_id|currency}}
                </td>

                <td rowspan="{{$nbActes.$sejour_id}}">{{tr}}CSejour._type_admission.{{$sejour->type}}{{/tr}}</td>

                <td class="text" rowspan="{{$nbActes.$sejour_id}}">
                  {{if $sejour->_ref_actes|@count && ($order == 'sortie_reelle' || ($order == 'acte_execution' && $sejour->_guid|in_array:$dates_actes.$key))}}
                    <div onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')" style="min-height: {{math equation="(x/y)*100" x=$sejour->_ref_actes|@count y=$nbActes.$sejour_id}}%;">
                    Sejour du {{mb_value object=$sejour field=entree}}
                      au {{mb_value object=$sejour field=sortie}}
                    </div>
                  {{/if}}
                  {{foreach from=$sejour->_ref_operations item=operation}}
                    {{if $operation->_ref_actes|@count && ($order == 'sortie_reelle' || ($order == 'acte_execution' && $operation->_guid|in_array:$dates_actes.$key))}}
                      <div onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}')" style="min-height: {{math equation="(x/y)*100" x=$operation->_ref_actes|@count y=$nbActes.$sejour_id}}%;">
                        <br/>Intervention du {{mb_value object=$operation field=_datetime_best}}
                        {{if $operation->libelle}}<br /> {{$operation->libelle}}{{/if}}
                      </div>
                    {{/if}}
                  {{/foreach}}
                  {{foreach from=$sejour->_ref_consultations item=consult}}
                    {{if $consult->_ref_actes|@count && ($order == 'sortie_reelle' || ($order == 'acte_execution' && $consult->_guid|in_array:$dates_actes.$key))}}
                      <div onmouseover="ObjectTooltip.createEx(this, '{{$consult->_guid}}')" style="min-height: {{math equation="(x/y)*100" x=$consult->_ref_actes|@count y=$nbActes.$sejour_id}}%;">
                        <br/>Consultation du {{$consult->_datetime|date_format:"%d %B %Y"}}
                        {{if $consult->motif}}: {{$consult->motif}}{{/if}}
                      </div>
                    {{/if}}
                  {{/foreach}}
                </td>

                {{assign var=see_actes value=0}}
                {{assign var=see_facture value=1}}
                {{mb_include module=dPplanningOp template=inc_acte_realise codable=$sejour}}
                {{math equation="x+y" x=$sejour->_ref_actes|@count y=$see_actes assign=see_actes}}
                {{if $sejour->_ref_actes|@count && $see_facture}}
                  {{assign var=count value=0}}
                  {{foreach from=$sejour->_ref_actes item="acte" name="tab_codable"}}
                    {{if $acte->executant_id|in_array:$prat_ids && ($order == 'sortie_reelle' || ($order == 'acte_execution' && 'Ox\Core\CMbDT::date'|static_call:$acte->execution == $key))}}
                      {{math assign=count equation="x+1" x=$count}}
                    {{/if}}
                    {{if $count == 1 && $use_fact_etab && $see_facture}}
                      {{assign var=see_facture value=0}}
                    {{/if}}
                  {{/foreach}}
                {{/if}}

                {{if $sejour->_ref_operations}}
                  {{foreach from=$sejour->_ref_operations item=operation}}
                    {{mb_include module=dPplanningOp template=inc_acte_realise codable=$operation}}
                    {{math equation="x+y" x=$operation->_ref_actes|@count y=$see_actes assign=see_actes}}
                    {{if $operation->_ref_actes|@count && $see_facture}}
                      {{assign var=count value=0}}
                      {{foreach from=$operation->_ref_actes item="acte" name="tab_codable"}}
                        {{if $acte->executant_id|in_array:$prat_ids && ($order == 'sortie_reelle' || ($order == 'acte_execution' && 'Ox\Core\CMbDT::date'|static_call:$acte->execution == $key))}}
                          {{math assign=count equation="x+1" x=$count}}
                        {{/if}}
                        {{if $count == 1 && $use_fact_etab && $see_facture}}
                          {{assign var=see_facture value=0}}
                        {{/if}}
                      {{/foreach}}
                    {{/if}}
                  {{/foreach}}
                {{/if}}

                {{if $sejour->_ref_consultations}}
                  {{foreach from=$sejour->_ref_consultations item=consult}}
                    {{mb_include module=dPplanningOp template=inc_acte_realise codable=$consult}}
                    {{math equation="x+y" x=$consult->_ref_actes|@count y=$see_actes assign=see_actes}}
                    {{if $consult->_ref_actes|@count && $see_facture}}
                      {{foreach from=$consult->_ref_actes item="acte" name="tab_codable"}}
                        {{assign var=count value=0}}
                        {{if $acte->executant_id|in_array:$prat_ids && ($order == 'sortie_reelle' || ($order == 'acte_execution' && 'Ox\Core\CMbDT::date'|static_call:$acte->execution == $key))}}
                          {{math assign=count equation="x+1" x=$count}}
                        {{/if}}
                        {{if $count == 1 && $use_fact_etab && $see_facture}}
                          {{assign var=see_facture value=0}}
                        {{/if}}
                      {{/foreach}}
                    {{/if}}
                  {{/foreach}}
                {{/if}}
              {{if $see_actes == 0}}
                </tr>
              {{/if}}
            </tbody>
            {{/foreach}}
          </table>
        </td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>
