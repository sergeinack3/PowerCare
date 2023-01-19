<table>
    {{if $allergies}}
        <thead>
        <tr>
            <th>Date</th>
            <th>Allergie</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$allergies item=_allergy}}
            {{assign var=values value='-'|explode:$_allergy->date}}
            {{assign var=year value=$values[0]}}
            {{assign var=month value=$values[1]}}
            {{assign var=day value=$values[2]}}

            {{if $month == '00'}}
                {{assign var=month value='01'}}
            {{/if}}

            {{if $day == '00'}}
                {{assign var=day value='01'}}
            {{/if}}

            {{assign var=date value="$year-$month-$day"}}

            <tr>
                <td>{{$date}}</td>
                <td>
                    <content ID='{{$_allergy->_guid}}'>{{$_allergy->rques}}</content>
                </td>
            </tr>
        {{/foreach}}
        </tbody>
    {{else}}
        <tr>
            <td>
                <content ID='{{'Ox\Interop\Cda\CCDAFactory'|const:NONE_ALLERGY}}'>Aucune allergie / intolérance /
                    réaction adverse
                </content>
            </td>
        </tr>
    {{/if}}
</table>
