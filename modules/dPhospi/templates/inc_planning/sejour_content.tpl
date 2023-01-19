{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Sejour -->

{{if $filter->_by_date}}
  <td>{{$curr_sejour->_ref_praticien}}</td>
{{/if}}
<td>{{$curr_sejour->$horodatage|date_format:$conf.time}}</td>
<td>
  {{if !$curr_sejour->facturable}}
    <strong>NF</strong>
  {{/if}}

  {{$curr_sejour->type|truncate:1:""|capitalize}}
</td>
<td>{{$curr_sejour->_duree_prevue}} j</td>
<td class="text">{{$curr_sejour->convalescence|nl2br}}</td>
<td class="text">
  {{mb_include module=hospi template=inc_placement_sejour sejour=$curr_sejour}}

  ({{tr}}chambre_seule.{{$curr_sejour->chambre_seule}}{{/tr}})
</td>
{{if $prestation->_id}}
  <td>
    {{mb_include module=hospi template=inc_vw_liaisons_prestation liaisons=$curr_sejour->_liaisons_for_prestation}}
  </td>
{{/if}}
<td class="text compact">{{$curr_sejour->rques|nl2br}}</td>
{{if $filter->_notes}}
  <td class="text compact">
    {{if $curr_sejour->_ref_notes|@count}}
      <ul>
        {{foreach from=$curr_sejour->_ref_notes item=_note}}
          <li>
            <span style="color: #333">{{$_note->libelle}} :</span> {{$_note->text}}
          </li>
        {{/foreach}}
      </ul>
    {{/if}}
  </td>
{{/if}}
