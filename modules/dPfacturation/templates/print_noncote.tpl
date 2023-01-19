{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td>
      <table>
        <tr>
          <th>
            <a href="#" onclick="window.print()">
              {{tr}}Report{{/tr}}
              {{mb_include module=system template=inc_interval_date from=$filter->_date_min to=$filter->_date_max}}
            </a>
          </th>
        </tr>
        {{foreach from=$listPrat item=_prat}}
        <tr>
          <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat}}</td>
        </tr>
        {{/foreach}}
      </table>
    </td>
    <td colspan="2">
      <table>
        {{foreach from=$listConsults_date key=key_date item=consultations}}
          <tr>
            <td colspan="2">
              <strong>{{tr var1=$key_date|date_format:$conf.longdate}}CFactureCabinet-no_cote_of %s{{/tr}}</strong>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <table class="tbl">
                <tr>
                  <th>{{mb_label class=CConsultation field=_prat_id}}</th>
                  <th>{{mb_label class=CConsultation field=patient_id}}</th>
                  <th>{{mb_label class=CPlageconsult field=date}}</th>
                  <th>{{mb_label class=CConsultation field=heure}}</th>
                </tr>
                {{foreach from=$consultations.consult item=consultation}}
                  {{assign var=_plage_consult value=$consultation->_ref_plageconsult}}
                  <tr>
                    <td class="text">
                      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_plage_consult->_ref_chir}}
                      {{if $_plage_consult->remplacant_id}}
                        {{tr}}CConsultation.replaced_by{{/tr}}
                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_plage_consult->_ref_remplacant}}
                      {{elseif $_plage_consult->pour_compte_id}}
                        {{tr}}CPlageConsult-pour_compte_of{{/tr}}
                        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_plage_consult->_ref_pour_compte}}
                      {{/if}}
                    </td>
                    <td class="text">
                      {{mb_include module=system template=inc_vw_mbobject object=$consultation->_ref_patient}}
                    </td>
                    <td class="text">
                      {{mb_value object=$_plage_consult field=date}}
                    </td>
                    <td>{{mb_value object=$consultation field=heure}}</td>
                  </tr>
                {{/foreach}}
              </table>
            </td>
          </tr>
          {{foreachelse}}
          <tr>
            <th>{{tr}}CFactureCabinet-no_cote-none_for_periode{{/tr}}</th>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>
