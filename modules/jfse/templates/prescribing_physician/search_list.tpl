{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$matches item=match}}
    <tr id="search-list-match-{{$match->id}}" data-id="{{$match->id}}"
        data-invoicing-number="{{$match->invoicing_number}}"
        data-national-id="{{$match->national_id}}"
        data-structure-id="{{$match->structure_id}}">
        <td class="identity"
            data-last-name="{{$match->last_name}}"
            data-first-name="{{$match->first_name}}">{{$match}}</td>
        <td class="speciality" data-speciality="{{$match->speciality}}">{{$match->speciality_label}}</td>
        <td class="type" data-type="{{$match->type}}">{{$match->type_label}}</td>
        <td class="narrow">
            <button type="button" class="tick me-primary notext"
                    onclick="PrescribingPhysician.selectPhysician($('search-list-match-{{$match->id}}'))">
                {{tr}}Select{{/tr}}
            </button>
            <button class="trash notext"
                    type="button"
                    onclick="PrescribingPhysician.deletePrescribingPhysician('{{$match->id}}', getForm('prescribing_physician_search_form'))">
                {{tr}}Delete{{/tr}}
            </button>
        </td>
    </tr>
    {{foreachelse}}
    <tr>
        <td colspan="4" class="empty">
            {{tr}}No result{{/tr}}
        </td>
    </tr>
{{/foreach}}
