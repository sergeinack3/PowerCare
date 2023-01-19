<list>
    {{foreach from=$antecedents key=_family_link item=_antecedents}}
        <item>
            <content ID="{{$_family_link}}">{{tr}}CAntecedent.family_link.{{$_family_link}}{{/tr}}</content>

                {{foreach from=$_antecedents item=_antecedent}}
                {{assign var=values value='-'|explode:$_antecedent->date}}
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
                    {{$date}}
                    <content ID="{{$_antecedent->_guid}}">{{$_antecedent->rques|smarty:nodefaults|purify}}</content>
            {{/foreach}}

        </item>
    {{/foreach}}
</list>
