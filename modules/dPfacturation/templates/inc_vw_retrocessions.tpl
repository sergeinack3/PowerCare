{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function printRetrocession() {
    var url = new Url("facturation", "ajax_vw_retrocessions");
    url.addParam("print", 1);
    url.popup(900, 600, "Retrocession");
  }
  {{if !$print}}
  Main.add(function () {
    PairEffect.initGroup("serviceEffect");
  });
  {{/if}}
</script>
{{if isset($factures|smarty:nodefaults)}}
  {{if $print}}
    <h2 style="text-align: center;">
      {{tr}}CRetrocession{{/tr}} {{tr}}date.from{{/tr}} {{$filter->_date_min|date_format:$conf.date}}
      {{tr}}date.to{{/tr}} {{$filter->_date_max|date_format:$conf.date}}
      {{if $prat->_id}}
        <br/> {{tr}}CFacture-praticien_id{{/tr}}: {{$prat->_view}}
      {{/if}}
    </h2>
  {{/if}}
  <table class="tbl">
    {{if !$print}}
      <tr>
        <th colspan="7" class="title">
          {{tr}}CRetrocession{{/tr}} {{tr}}date.from{{/tr}} {{$filter->_date_min|date_format:$conf.date}}
          {{tr}}date.to{{/tr}} {{$filter->_date_max|date_format:$conf.date}}
        </th>
        <th class="title">
          <button type="button" class="print me-tertiary" onclick="printRetrocession();">{{tr}}Print{{/tr}}</button>
        </th>
      </tr>
    {{/if}}
    <tr>
      <th style="width:10px;"></th>
      <th class="narrow">{{tr}}CFacture{{/tr}}</th>
      <th class="narrow">{{tr}}CFacture-date_cloture{{/tr}}</th>
      <th class="narrow">{{tr}}CFacture-praticien_id{{/tr}}</th>
      <th>{{tr}}CPatient{{/tr}}</th>
      <th>{{tr}}CFactureCabinet-montant_total{{/tr}}</th>
      <th>{{tr}}CRetrocession{{/tr}}</th>
      <th class="narrow">{{tr}}common-Result{{/tr}}</th>
    </tr>
    {{foreach from=$factures item=facture}}
      <tr>
        <td id="{{$facture->_guid}}-trigger"></td>
        <td>
          {{if !$print}}
            <a href="#" onmouseover="ObjectTooltip.createEx(this, '{{$facture->_guid}}')">
            {{$facture->_view}}
            </a>
          {{else}}
            {{$facture->_view}}
          {{/if}}
          {{if $facture->_current_fse}}({{tr}}CConsultation-back-fses{{/tr}}: {{$facture->_current_fse_number}}){{/if}}
        </td>
        <td style="text-align: center;">{{$facture->cloture|date_format:$conf.date}}</td>
        <td>
          {{if !$print}}
            <a href="#" onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_praticien->_guid}}')">
              {{$facture->_ref_praticien->_view}}
            </a>
          {{else}}
            {{$facture->_ref_praticien->_view}}
          {{/if}}
        </td>
        <td>
          {{if !$print}}
            <a href="#" onmouseover="ObjectTooltip.createEx(this, '{{$facture->_ref_patient->_guid}}')">
              {{$facture->_ref_patient->_view}}
            </a>
          {{else}}
            {{$facture->_ref_patient->_view}}
          {{/if}}
        </td>
        <td style="text-align: right;"><b>{{$facture->_montant_avec_remise|string_format:"%0.2f"|currency}}</b></td>
        <td style="text-align: right;"><b>{{$facture->_montant_retrocession|string_format:"%0.2f"|currency}}</b></td>
        <td></td>
      </tr>
      <tbody class="serviceEffect" id="{{$facture->_guid}}">
        {{foreach from=$facture->_retrocessions item=retro key=key}}
          <tr>
            <td colspan="4"></td>
            <td style="text-align:right;">{{$key}}</td>
            {{foreach from=$retro item=montant}}
              <td style="text-align: right;">{{$montant|string_format:"%0.2f"|currency}}</td>
            {{/foreach}}
            <td></td>
          </tr>
        {{/foreach}}
      </tbody>
    {{foreachelse}}
      <tr>
        <td colspan="8" class="empty">{{tr}}CFactureCabinet.none{{/tr}}</td>
      </tr>
    {{/foreach}}

    {{if count($factures)}}
      <tr style="text-align: right;">
        <td colspan="5"><strong>{{tr}}Total{{/tr}}</strong></td>
        <td><strong>{{$total_montant|string_format:"%0.2f"|currency}}</strong></td>
        <td><strong>{{$total_retrocession|string_format:"%0.2f"|currency}}</strong></td>
        <td><strong>{{$total_montant-$total_retrocession|string_format:"%0.2f"|currency}}</strong></td>
      </tr>
    {{/if}}
  </table>
{{/if}}
