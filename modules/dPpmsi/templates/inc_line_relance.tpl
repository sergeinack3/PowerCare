{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=sejour value=$_relance->_ref_sejour}}
{{assign var=patient value=$_relance->_ref_patient}}
{{assign var=chir value=$_relance->_ref_chir}}
<tr>
    <td class="not-printable">
        <button type="button" class="edit notext"
                onclick="Relance.edit('{{$_relance->_id}}', null, Relance.searchRelances);">{{tr}}pmsi-edit_relance{{/tr}}
        </button>
    </td>
    <td>
        {{$sejour->_NDA}}
    </td>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
          {{$patient}}
        </span>
    </td>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
          {{mb_value object=$sejour field=entree}}
        </span>
    </td>
    <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
          {{mb_value object=$sejour field=sortie}}
        </span>
    </td>
    <td>
        {{if $sejour->sortie_reelle}}
            {{tr}}common-Completed-court{{/tr}}
        {{else}}
            {{tr}}common-In progress{{/tr}}
        {{/if}}
    </td>
    <td class="text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir}}
    </td>
    <td>
        {{if $_relance->datetime_cloture}}
            {{tr}}common-Closed|f{{/tr}}
        {{elseif $_relance->datetime_relance}}
            {{tr}}pmsi-2_relance{{/tr}}
        {{else}}
            {{tr}}config-dPpmsi-relances{{/tr}}
        {{/if}}
    </td>
    {{foreach from='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs" item=doc}}
        {{if "dPpmsi relances $doc"|gconf}}
            <td style="text-align: center;">
                {{if $_relance->$doc}}
                    <span {{if $doc == "autre"}}title="{{$_relance->description}}" style="cursor: pointer;"{{/if}}>
                X
              </span>
                {{/if}}
            </td>
        {{/if}}
    {{/foreach}}
    <td class="text">
        {{mb_value object=$_relance field=commentaire_dim}}
    </td>
    <td class="text">
        {{mb_value object=$_relance field=commentaire_med}}
    </td>
    <td>
        {{mb_value object=$_relance field=urgence}}
    </td>
    <td>
        {{if !$print}}
            {{assign var=sejour_id value=$_relance->_ref_sejour->_id}}
            {{assign var=sejour_class value=$_relance->_ref_sejour->_class}}
            {{if $notReadFiles[$sejour_id]|@count}}
                <span onmouseover="ObjectTooltip.createDOM(this, 'tooltip_file_{{$sejour_id}}')">
                        {{$notReadFiles[$sejour_id]|@count}}
                    </span>
                <div style="display: none" id="tooltip_file_{{$sejour_id}}">
                    {{mb_include module=files template=inc_read_file object_id=$sejour_id object_class=$sejour_class documents=$notReadFiles[$sejour_id]}}
                </div>
            {{/if}}
        {{/if}}
    </td>
</tr>
