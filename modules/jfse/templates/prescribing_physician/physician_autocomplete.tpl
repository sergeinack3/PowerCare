{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul style="text-align: left">
    {{foreach from=$matches item=match}}
        <li id="autocomplete-{{$match->id}}"
            data-id="{{$match->id}}"
            data-invoicing-number="{{$match->invoicing_number}}"
            data-national-id="{{$match->national_id}}"
            data-structure-id="{{$match->structure_id}}"
        >
            <p class="identity"
               data-last-name="{{$match->last_name}}"
               data-first-name="{{$match->first_name}}">
                {{$match}}
            </p>
            <p style="color: #999; font-size: 0.8em;">
                {{tr}}CPrescribingPhysician-speciality{{/tr}}:
                <span class="speciality" data-speciality="{{$match->speciality}}">{{$match->speciality_label}}</span>
                {{if $match->type_label}}
                    <br>
                    {{tr}}CPrescribingPhysician-type{{/tr}}:
                {{/if}}

                {{* Keep the type to still give access to 'data-type' to the js *}}
                <span class="type" data-type="{{$match->type}}">{{$match->type_label}}</span>
                <br>
                {{tr}}CPrescribingPhysician-invoicing_number{{/tr}}:
                <span>{{$match->invoicing_number}}</span>
            </p>
        </li>
    {{foreachelse}}
        <li>
            <span class="informal">
              <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
            </span>
        </li>
    {{/foreach}}
</ul>
