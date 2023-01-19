{{*
 * @package Mediboard\Cabinet
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
            <a href="#" onclick="window.print()">
              Rapport
              {{mb_include module=system template=inc_interval_date from=$filter->_date_min to=$filter->_date_max}}
            </a>
          </th>
        </tr>

        <!-- Praticiens concernés -->
        {{foreach from=$listPrat item=_prat}}
        <tr>
          <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat}}</td>
        </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
  {{foreach from=$listPlages item=_plage}}
    {{if !$ajax}} 
      <tbody id="{{$_plage->_guid}}">
    {{/if}}
    <tr>
      <td colspan="2">
        <strong onclick="PlageConsult.refresh('{{$_plage->_id}}')">
          {{if $_plage->_ref_remplacant->_id}}
            {{tr}}CPlageConsult.remplacement_of{{/tr}} {{$_plage->_ref_chir}}
          {{elseif $_plage->_ref_pour_compte->_id}}
            Pour le compte de {{$_plage->_ref_pour_compte}}
          {{/if}}
          &mdash; {{$_plage->date|date_format:$conf.longdate}}
          de {{$_plage->debut|date_format:$conf.time}} 
          à  {{$_plage->fin|date_format:$conf.time}} 
  
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
            <th>{{mb_label class=CConsultation field=patient_id}}</th>
            <th>Praticien</th>
            <th>{{mb_label class=CConsultation field=tarif}}</th>
            <th>Montant</th>
            <th>Rétrocession ({{mb_value object=$_plage field=pct_retrocession}})</th>
          </tr>
          {{foreach from=$_plage->_ref_consultations item=_consultation}}
            <tr>
              <td class="text">
                <a name="consult-{{$_consultation->_id}}">
                  {{assign var=patient value=$_consultation->_ref_patient}}
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
                    {{$patient}}
                  </span>
                </a>
              </td>
              <td>
                {{if $_plage->_ref_remplacant->_id}}
                  {{mb_include module=mediusers template=inc_vw_mediuser  mediuser=$_plage->_ref_remplacant}}
                {{elseif $_plage->_ref_pour_compte->_id}}
                  {{mb_include module=mediusers template=inc_vw_mediuser  mediuser=$_plage->_ref_chir}}
                {{/if}}
              </td>
              <td class="text">
                {{if $_consultation->tarif}} 
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_consultation->_guid}}')">
                  {{$_consultation->tarif}}
                </span>
                {{/if}}
              </td>
              <td class="text">
                {{$_consultation->du_patient}}
              </td>
              <td>
                <strong>{{$_consultation->du_patient*$_plage->pct_retrocession/100|currency}}</strong>
              </td>
            </tr>
          {{/foreach}}
          <tr>
            <td colspan="4" style="text-align: right" >
              <strong>{{tr}}Total{{/tr}}</strong>
            </td>
            {{assign var=plage_id value=$_plage->_id}}
            <td><strong>{{$plages.$plage_id.total|currency}}</strong></td>
          </tr>
        </table>
      </td>
    </tr>
    {{if !$ajax}} 
      </tbody>
    {{/if}}
  {{/foreach}} 
</table>