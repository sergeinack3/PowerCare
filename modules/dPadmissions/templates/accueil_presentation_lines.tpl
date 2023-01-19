{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<thead>
  <tr>
    <th class="narrow">{{mb_title class=CPatient field=civilite}}</th>
    <th style="width: 12.5%">{{mb_title class=CPatient field=nom}}</th>
    <th style="width: 12.5%">{{mb_title class=CPatient field=prenom}}</th>
    <th style="width: 12.5%">{{mb_title class=CPatient field=naissance}}</th>
    <th style="width: 12.5%">{{mb_title class=CSejour  field=entree}}</th>
    <th style="width: 12.5%">{{tr}}CService{{/tr}} - {{tr}}CChambre{{/tr}}</th>
    <th>{{mb_title class=COperation field=_status}}</th>
  </tr>
</thead>
{{foreach from=$sejours_pages item=_sejours_list name=sejour_page_loop}}
  <tbody id="sejour_page_{{$smarty.foreach.sejour_page_loop.index}}" class="sejour-page"
    style="{{if !$smarty.foreach.sejour_page_loop.first}}display:none{{/if}}">
  {{foreach from=$_sejours_list item=_sejour}}
    {{assign var=patient value=$_sejour->_ref_patient}}
    {{assign var=lit value=$_sejour->_ref_curr_affectation->_ref_lit}}
    {{assign var=cleanup value=$lit->_ref_last_cleanup}}
    <tr>
      <td>{{mb_value object=$patient field=civilite}}</td>
      <td style="font-weight: bold">{{mb_value object=$patient field=nom}}</td>
      <td style="font-weight: bold">{{mb_value object=$patient field=prenom}}</td>
      <td>{{$patient->naissance}}</td>
      <td>{{$_sejour->entree|date_format:"%Hh%M"}}</td>
      <td>
        {{$lit->_ref_chambre->_ref_service field=nom}} - {{mb_value object=$lit->_ref_chambre field=nom}}
      </td>
      <td class="accueil-presentation-etat">
        {{if $cleanup}}
          <span class="cleanup-status-{{$cleanup->_status}}">
            {{tr}}admissions-presentation room-{{$cleanup->_status}}{{/tr}}
            {{if $cleanup->_waiting_time}}
              ({{$cleanup->_waiting_time}})
            {{/if}}
          </span>
        {{else}}
          {{tr}}CSejour.recuse.-1{{/tr}}
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
      <tr>
        <td colspan="7" class="empty">{{tr}}CPatient.none{{/tr}}</td>
      </tr>
  {{/foreach}}
  </tbody>
{{foreachelse}}
  <tbody class="sejour-page">
    <tr>
      <td colspan="7" class="empty">{{tr}}CPatient.none{{/tr}}</td>
    </tr>
  </tbody>
{{/foreach}}
<tr>
  <td colspan="7">
    <div id="accueil_presentation_vignettes">
      {{foreach from=$sejours_pages item=_sejours_list name=sejour_vignettes_loop}}
        <div class="{{if $smarty.foreach.sejour_vignettes_loop.first}}selectionnee{{/if}}">
          {{$smarty.foreach.sejour_vignettes_loop.iteration}}
        </div>
      {{/foreach}}
    </div>
  </td>
</tr>