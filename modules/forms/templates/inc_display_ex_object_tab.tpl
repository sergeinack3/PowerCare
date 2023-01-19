{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=limit value=9}}
{{unique_id var=ex_object_uid}}

<script>
  Main.add(function(){
    ExObject.displayExObjectTab($("ex-link-dates-{{$ex_object_uid}}").down("[data-ex_class_id]"), 'ex-object-{{$ex_object_uid}}');
    Control.Tabs.setTabCount("{{$tab_id}}", {{$ex_links|@count}});
  });
</script>

<div class="ex-link-dates" id="ex-link-dates-{{$ex_object_uid}}">
  {{if !$readonly}}
    <button class="new"
            data-ex_class_id="{{$ex_class->_id}}"
            data-reference_guid="{{$reference->_guid}}"
            data-ex_object_id=""
            data-event_name="{{$event_name}}"
            data-tab_show_header="{{$tab_show_header}}"
            data-readonly="{{$readonly}}"
            onclick="ExObject.displayExObjectTab(this, 'ex-object-{{$ex_object_uid}}')">
      {{tr}}New{{/tr}}
    </button>
  {{/if}}
  
  {{foreach from=$ex_links item=_ex_link name=_ex_link}}
    {{if $smarty.foreach._ex_link.index < $limit || $ex_links|@count == $limit+1}}
      <a href="#" 
         data-ex_class_id="{{$_ex_link->ex_class_id}}"
         data-reference_guid="{{$reference->_guid}}"
         data-ex_object_id="{{$_ex_link->ex_object_id}}"
         data-event_name="{{$event_name}}"
         data-tab_show_header="{{$tab_show_header}}"
         data-readonly="{{$readonly}}"
         onclick="ExObject.displayExObjectTab(this, 'ex-object-{{$ex_object_uid}}')">
        {{mb_value object=$_ex_link field=datetime_create}}
      </a>
      
      {{if !$smarty.foreach._ex_link.last}}
        &bull;
      {{/if}}
    {{/if}}
  {{/foreach}}
  
  {{if $ex_links|@count > $limit+1}}
    <select onchange="ExObject.displayExObjectTab(this, 'ex-object-{{$ex_object_uid}}')">
      <option disabled selected> &mdash; </option>
      {{foreach from=$ex_links item=_ex_link name=_ex_link}}
        {{if $smarty.foreach._ex_link.index >= $limit}}
          <option value="{{$_ex_link->_id}}"
                  data-ex_class_id="{{$_ex_link->ex_class_id}}"
                  data-reference_guid="{{$reference->_guid}}"
                  data-ex_object_id="{{$_ex_link->ex_object_id}}"
                  data-event_name="{{$event_name}}"
                  data-tab_show_header="{{$tab_show_header}}"
                  data-readonly="{{$readonly}}">
            {{mb_value object=$_ex_link field=datetime_create}}
          </option>
        {{/if}}
      {{/foreach}}
    </select>
  {{/if}}
</div>

<div id="ex-object-{{$ex_object_uid}}"></div>