{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td class="halfPane">
      <table>
        <tr>
          <th>
            <button type="button" class="notext print not-printable" onclick="window.print();">{{tr}}Print{{/tr}}</button>
            {{tr}}compta-print_tva-title{{/tr}}
              {{mb_include module=system template=inc_interval_date from=$date_min to=$date_max}}
          </th>
        </tr>
        {{foreach from=$listPrat item=_prat}}
          <tr>
            <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>

    <td class="halfPane">
      <table class="tbl" style="width:400px;float:right;">
        <tr>
          <th class="title" colspan="3">{{tr}}compta-recapitulatif{{/tr}}</th>
        </tr>
        <tr>
          <th class="narrow">{{tr}}Rate{{/tr}} (en %)</th>
          <th class="narrow">{{tr}}CFacture-nb|pl{{/tr}}</th>
          <th>{{tr}}Total{{/tr}}</th>
        </tr>
        {{foreach from=$list_taux item=taux}}
          <tr>
            <th>{{$taux|string_format:"%0.1f"}}</th>
            <td style="text-align:center;">{{$taux_factures.$taux.count}}</td>
            <td style="text-align:right;">{{$taux_factures.$taux.total|string_format:"%0.2f"|currency}}</td>
          </tr>
        {{/foreach}}
        <tr>
          <th>{{tr}}Total{{/tr}}</th>
          <td style="text-align: center;"><b>{{$nb_factures}}</b></td>
          <td style="text-align:right;"><b>{{$total_tva|string_format:"%0.2f"|currency}}</b></td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- Les factures-->
  {{foreach from=$list_taux item=taux}}
    {{if $taux_factures.$taux.factures|@count}}
      <tr>
        <td colspan="2"><strong>{{tr}}Rate{{/tr}}: {{$taux}} %</strong></td>
      </tr>
      <tr>
        <td colspan="2">
          <table class="tbl">
            <tr>
              <th class="narrow text">{{tr}}CFactureCabinet{{/tr}}</th>
              <th style="width: 15%;">{{mb_label class=CConsultation field=_prat_id}}</th>
              <th style="width: 15%;">{{mb_label class=CConsultation field=patient_id}}</th>
              <th style="width: 15%;">{{mb_label class=CConsultation field=_date}}</th>
              <th style="width: 15%;">{{mb_label class=CConsultation field=secteur3}}</th>
              <th>{{tr}}CConsultation-taux_tva-court{{/tr}} ({{$taux}} %)</th>
              <th>{{tr}}CFacture-HT{{/tr}}</th>
              <th>{{tr}}CFacture-TTC{{/tr}}</th>
            </tr>
            {{foreach from=$taux_factures.$taux.factures item=facture}}
              <tr>
                <td>
                  <strong onmouseover="ObjectTooltip.createEx(this, '{{$facture->_guid}}')">
                    {{$facture}}
                    {{if $facture->_current_fse}}({{tr}}CConsultation-back-fses{{/tr}}: {{$facture->_current_fse_number}}){{/if}}
                  </strong>
                </td>
                <td> {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$facture->_ref_praticien}} </td>
                <td>
                  {{mb_include module=system template=inc_vw_mbobject object=$facture->_ref_patient}}
                </td>
                <td>{{mb_value object=$facture field=ouverture}}</td>
                <td style="text-align: right;">{{mb_value object=$facture field=_secteur3 format=currency}}</td>
                <td style="text-align: right;">{{mb_value object=$facture field=du_tva format=currency}}</td>
                <td style="text-align: right;">{{$facture->_montant_avec_remise-$facture->du_tva|currency}}</td>
                <td style="text-align: right;">{{mb_value object=$facture field=_montant_avec_remise format=currency}}</td>
              </tr>
            {{/foreach}}
            <tr style="text-align: right;">
              <td colspan="4"> <strong>{{tr}}Total{{/tr}}</strong> </td>
              <td><strong>{{$taux_factures.$taux.totalst|currency}} </strong></td>
              <td><strong>{{$taux_factures.$taux.total|currency}}   </strong></td>
              <td><strong>{{$taux_factures.$taux.totalht|currency}} </strong></td>
              <td><strong>{{$taux_factures.$taux.totalttc|currency}}</strong></td>
            </tr>
          </table>
        </td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
