{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="halfPane category">{{tr}}CPatient{{/tr}}</th>
    <th class="category">{{tr}}CSejour{{/tr}}</th>
  </tr>
  <tr>
    <td class="text">
      {{assign var=dossier_medical value=$sejour->_ref_patient->_ref_dossier_medical}}
      <ul>
        <!-- Traitements personnels du patient -->
        {{if $dossier_medical->_ref_prescription}}
          {{foreach from=$dossier_medical->_ref_prescription->_ref_prescription_lines item=_line_med}}
            <li>
              <a href="#1" onclick="Prescription.viewProduit(null,'{{$_line_med->code_ucd}}','{{$_line_med->code_cis}}');">
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
                <span class="compact">({{mb_include module=system template=inc_interval_date from=$_line_med->debut to=$_line_med->fin}})</span>
              {{/if}}
            </li>
          {{/foreach}}
        {{/if}}
        <hr/>
        {{foreach from=$dossier_medical->_ref_traitements item=curr_trmt}}
          <li>
            {{if $curr_trmt->fin}}
              Depuis {{mb_value object=$curr_trmt field=debut}}
              jusqu'à {{mb_value object=$curr_trmt field=fin}} :
            {{elseif $curr_trmt->debut}}
              Depuis {{mb_value object=$curr_trmt field=debut}} :
            {{/if}}
            <em>{{$curr_trmt->traitement}}</em>
          </li>
          {{foreachelse}}
          {{if $dossier_medical->absence_traitement}}
            <li class="empty">{{tr}}CTraitement.absence{{/tr}}</li>
          {{else}}
            <li class="empty">{{tr}}CTraitement.none{{/tr}}</li>
          {{/if}}
        {{/foreach}}
      </ul>
    </td>
    <td class="text">
      <ul>
        {{foreach from=$dossier_medical->_ref_traitements item=curr_trmt}}
          <li>
            {{if $curr_trmt->fin}}
              Depuis {{mb_value object=$curr_trmt field=debut}}
              jusqu'à {{mb_value object=$curr_trmt field=fin}} :
            {{elseif $curr_trmt->debut}}
              Depuis {{mb_value object=$curr_trmt field=debut}} :
            {{/if}}
            <em>{{$curr_trmt->traitement}}</em>
          </li>
          {{foreachelse}}
          <li class="empty">{{tr}}CTraitement.none{{/tr}}</li>
        {{/foreach}}
      </ul>
    </td>
  </tr>
</table>