<list>
    {{foreach from=$pathologies item=_pathology}}
      <item>
          {{$_pathology->debut}} :
        <content ID="{{$_pathology->_guid}}">{{$_pathology->_view|smarty:nodefaults|purify}}</content>
      </item>
    {{/foreach}}
</list>
