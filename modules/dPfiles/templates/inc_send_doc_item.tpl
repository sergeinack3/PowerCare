{{mb_default var=disabled_share value=false}}

<div>
  <br />
  <button class="fas fa-share" id="button_share_doc" type="button" onclick="{{$onclick}}" {{if $disabled_share}}disabled{{/if}}>
      {{tr}}common-action-Share{{/tr}}
  </button>

  {{math assign=previous_step equation="x - 1" x=$step}}
  {{math assign=next_step     equation="x + 1" x=$step}}

  {{if $step > 0}}
    <button class="fa fa-chevron-circle-left" type="button"
            onclick="DocumentItem.refreshNavMenu({{$previous_step}}, {{$total}}, true, '{{$docItem->_guid}}');">
        {{tr}}common-action-Back{{/tr}}
    </button>
  {{/if}}

  {{if $step < $total -1}}
    <button class="fa fa-chevron-circle-right" type="button"
            onclick="DocumentItem.refreshNavMenu({{$next_step}}, {{$total}}, true, '{{$docItem->_guid}}');">
        {{tr}}common-action-Next{{/tr}}
    </button>
  {{/if}}
</div>