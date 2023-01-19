{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$curr_prat.sejours item=curr_sejour}}
  <tr>
    {{assign var=patient value=$curr_sejour->_ref_patient}}

    <td>{{$curr_sejour->$horodatage|date_format:$conf.time}}</td>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
        <strong>{{$patient->_view}}</strong>
      </span>
    </td>
    <td class="text">
      {{mb_value object=$patient field=naissance}} <br />({{$patient->_age}})
    </td>
    <td class="text">
      {{$patient->sexe}}
    </td>
    {{if $filter->_coordonnees}}
      <td>
        {{mb_value object=$patient field=adresse}}
        <br />
        {{mb_value object=$patient field=cp}}
        {{mb_value object=$patient field=ville}}
      </td>
      <td>
        {{mb_value object=$patient field=tel}}
        <br />
        {{mb_value object=$patient field=tel2}}
      </td>
    {{/if}}
    <td class="text">
      {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
        <span style="padding-left: 0;" onmouseover="ObjectTooltip.createEx(this, '{{$curr_operation->_guid}}');">
          {{if $curr_operation->libelle}}
            <strong>[{{$curr_operation->libelle}}]</strong>
          {{/if}}
        </span>
        <br />
      {{/foreach}}
    </td>
    <td>
      {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_operation->_ref_chir}}
        <br />
      {{/foreach}}
    </td>
    <td>
      {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_operation->_ref_anesth}}
        <br />
      {{/foreach}}
    </td>
    <td class="text compact">
      {{foreach from=$curr_sejour->_ref_operations item=curr_operation}}
        {{$curr_operation->rques}}
        <br />
      {{/foreach}}
    </td>
    <td class="text">
      {{mb_include module=hospi template=inc_placement_sejour sejour=$curr_sejour}}
    </td>
    <td>
      {{$curr_sejour->type|truncate:1:""|capitalize}}
    </td>
    <td>{{$curr_sejour->_duree_prevue}} j</td>
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
  </tr>
{{/foreach}}