{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div style="float: right;">
  <table class="tbl" style="text-align: center;">
    <tr>
      <th colspan="2" class="title">{{tr}}mod-dPfacturation-vw_compta_consults-title{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_label class=CConsultation field=_somme}}</th>
      <td>{{$recapReglement.facture|currency}}</td>
    </tr>
    <tr>
      <th>{{tr}}CFacture-total_regle{{/tr}}</th>
      <td>{{$recapReglement.regle|currency}}</td>
    </tr>
    <tr>
      <th>{{tr}}CFacture-total_no_regle{{/tr}}</th>
      <td>{{$recapReglement.non_regle|currency}}</td>
    </tr>
  </table>
</div>

<div>
  <strong>
    <button type="button" class="notext print not-printable" onclick="window.print();">{{tr}}Print{{/tr}}</button>
    {{tr}}Report{{/tr}}
    {{mb_include module=system template=inc_interval_date from=$filter->_date_min to=$filter->_date_max}}
  </strong>
</div>

<!-- Praticiens concernés -->
{{foreach from=$listPrat item=_prat}}
  <div>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat}}</div>
{{/foreach}}

<table class="main">
  {{foreach from=$plages item=_plage}}
    <tr>
      <td colspan="2">
        <br />
        <br />
        <strong>
          {{$_plage->_ref_chir}}
          {{if $_plage->_ref_pour_compte->_id}}
            {{tr}}CPlageConsult-pour_compte_of{{/tr}} {{$_plage->_ref_pour_compte->_view}}
          {{/if}}
          &mdash; {{$_plage->date|date_format:$conf.longdate}}
          {{tr}}from{{/tr}} {{$_plage->debut|date_format:$conf.time}}
          {{tr}}to{{/tr}}  {{$_plage->fin|date_format:$conf.time}}
          {{if $_plage->libelle}}
            : {{mb_value object=$_plage field=libelle}}
          {{/if}}
        </strong>
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <table class="tbl">
          <tr>
            <th colspan="2" class="narrow text">{{tr}}CFactureCabinet{{/tr}}</th>
            <th style="width: 30%;">{{mb_label class=CConsultation field=_prat_id}}</th>
            <th style="width: 30%;">{{mb_label class=CConsultation field=patient_id}}</th>
            <th style="width: 30%;">
              {{mb_label class=CConsultation field=_date}}:{{mb_label class=CConsultation field=tarif}}
            </th>
            <th class="narrow">{{tr}}CFacture-montant{{/tr}}</th>
            <th class="narrow">{{mb_label class=CFactureCabinet field=remise}}</th>
            <th class="narrow">{{tr}}CConsultation-_somme-court{{/tr}}</th>
            <th class="narrow">{{tr}}reglee{{/tr}}</th>
          </tr>

          {{foreach from=$_plage->_ref_consultations item=_consultation}}
            {{if !$tarif || $_consultation->tarif == $tarif->description}}
                {{assign var=facture value=$_consultation->_ref_facture}}
                <tr>
                  <td>
                    <strong onmouseover="ObjectTooltip.createEx(this, '{{$facture->_guid}}')">
                      {{$facture}}
                      {{if $facture->_current_fse}}({{tr}}CConsultation-back-fses{{/tr}}: {{$facture->_current_fse_number}}){{/if}}
                    </strong>
                    {{if $facture->group_id != $g}}
                      <span class="compact"><br />({{$facture->_ref_group}})</span>
                    {{/if}}
                  </td>
                  <td>{{mb_include module=system template=inc_object_notes object=$facture}}</td>
                  <td class="text">
                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_plage->_ref_chir}}
                  </td>

                  <td class="text">
                    {{mb_include module=system template=inc_vw_mbobject object=$_consultation->_ref_patient}}
                  </td>

                  <td class="text">
                    {{foreach from=$facture->_ref_consults item=_consult}}
                      <div>
                          <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
                            {{mb_value object=$_consult field=_date}}: {{mb_value object=$_consult field=tarif}}
                          </span>
                      </div>
                    {{foreachelse}}
                      <div class="empty">{{tr}}CConsultation.none{{/tr}}</div>
                    {{/foreach}}
                  </td>

                  <td style="text-align: right;">{{mb_value object=$facture field=_montant_sans_remise}}</td>
                  <td style="text-align: right;">{{mb_value object=$facture field=remise}}</td>
                  <td style="text-align: right;">{{mb_value object=$facture field=_montant_avec_remise}}</td>
                  <td style="text-align: right;">{{mb_value object=$facture field=_reglements_total}}</td>
                </tr>
            {{/if}}
          {{/foreach}}
          <tr>
            <td colspan="6"></td>
            <td><strong>{{tr}}Total{{/tr}}</strong></td>
            <td><strong>{{$_plage->_total.facture|currency}} </strong></td>
            <td><strong>{{$_plage->_total.regle|currency}}</strong></td>
          </tr>
        </table>
      </td>
    </tr>
  {{/foreach}}
</table>
