{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="me-w100" style="font-size: 100%;">
  <tr>
    <th class="category" colspan="4">{{tr}}CDossierPerinat-traitements_sejour_mere{{/tr}}</th>
  </tr>
  <tr>
    <td class="text">
        {{if $dossier_medical->_ref_traitements || $dossier_medical->_ref_prescription}}
          <ul>
              {{if $dossier_medical->_ref_prescription}}
                  {{foreach from=$dossier_medical->_ref_prescription->_ref_prescription_lines item=_line_med}}
                    <li>
                      <a href="#1"
                         onclick="Prescription.viewProduit(null,'{{$_line_med->code_ucd}}','{{$_line_med->code_cis}}');">
                          {{$_line_med->_ucd_view}}
                      </a>
                        {{if $_line_med->_ref_prises|@count}}
                          ({{foreach from=$_line_med->_ref_prises item=_prise name=foreach_prise}}
                            {{$_prise->_view}}{{if !$smarty.foreach.foreach_prise.last}},{{/if}}
                        {{/foreach}})
                        {{/if}}
                        {{if $_line_med->commentaire}}
                          ({{$_line_med->commentaire}})
                        {{/if}}
                        {{if $_line_med->debut || $_line_med->fin}}
                          <span
                            class="compact">({{mb_include module=system template=inc_interval_date from=$_line_med->debut to=$_line_med->fin}})</span>
                        {{/if}}
                    </li>
                  {{/foreach}}
              {{/if}}

              {{if $dossier_medical->_ref_traitements && $dossier_medical->_ref_traitements|@count}}
                  {{foreach from=$dossier_medical->_ref_traitements item=curr_trmt}}
                    <li>
                        {{if $curr_trmt->fin}}
                          {{tr}}common-Since{{/tr}} {{mb_value object=$curr_trmt field=debut}}
                          {{tr}}common-to{{/tr}} {{mb_value object=$curr_trmt field=fin}} :
                        {{elseif $curr_trmt->debut}}
                          {{tr}}common-Since{{/tr}} {{mb_value object=$curr_trmt field=debut}} :
                        {{/if}}
                      <i>{{$curr_trmt->traitement}}</i>
                    </li>
                      {{foreachelse}}
                      {{if $dossier_medical->absence_traitement}}
                        <li>{{tr}}CTraitement.absence{{/tr}}</li>
                      {{elseif !($dossier_medical->_ref_prescription && $dossier_medical->_ref_prescription->_ref_prescription_lines|@count) && !($lines_tp|@count)}}
                        <li>{{tr}}CTraitement.none{{/tr}}</li>
                      {{/if}}
                  {{/foreach}}
              {{/if}}
          </ul>
        {{/if}}
    </td>
  </tr>
</table>
