{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
    <div class="small-info">
        {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
    </div>
    {{mb_return}}
{{/if}}

{{mb_script module=maternite script=dossierMater register=true}}
{{mb_script module=maternite script=grossesse register=true}}

<table class="tbl">
    <tr>
        <th class="title text">
            {{mb_include module=system template=inc_object_idsante400}}
            {{mb_include module=system template=inc_object_history}}
            {{mb_include module=system template=inc_object_notes}}

            {{$object}}
        </th>
    </tr>
</table>

<table class="width100">
    {{mb_include module=maternite template=inc_vw_grossesse_resume}}
    <tr>
        <td class="button">
            <button class="grossesse" onclick="Grossesse.viewPlanningGrossesse('{{$object->_id}}')">
                {{tr}}CGrossesse-planning{{/tr}}
            </button>
            <button type="button" class="search"
                    onclick="DossierMater.printSummary('{{$object->_id}}');">
                {{tr}}CGrossesse-action-Summary sheet{{/tr}}
            </button>
        </td>
    </tr>
</table>
