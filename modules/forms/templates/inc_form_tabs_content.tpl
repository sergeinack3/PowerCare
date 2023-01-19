{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=readonly value=false}}

{{if !$form_tabs}}
  {{mb_return}}
{{/if}}

{{foreach from=$form_tabs item=_positions}}
  {{foreach from=$_positions item=_event}}
    {{assign var=host_object value=$_event->_host_object}}
    
    <div id="form-tab-{{$_event->_guid}}" style="display: none; position: relative;">
      <script>
        Main.add(function(){
          var tab = Control.Tabs.findByTabId("form-tab-{{$_event->_guid}}");
          
          var callback = function() {
            {{if $host_object->_id}}
              ExObject.displayTab(
                "form-tab-{{$_event->_guid}}", 
                "{{$host_object->_class}}",
                "{{$host_object->_id}}",
                "{{$_event->ex_class_id}}",
                "{{$_event->event_name}}",
                "{{$_event->tab_show_header}}",
                "{{$readonly}}"
              );
            {{/if}}
          };

          if (tab.activeContainer.id === "form-tab-{{$_event->_guid}}") {
            callback();
            tab.activeContainer._dontReload = true;
          }
          
          tab.observe("afterChange", function(container){
            if (container.id === "form-tab-{{$_event->_guid}}" && !container._dontReload) {
              callback();
              container._dontReload = true;
            }
          });
        });
      </script>
      
      {{if $_event->_tab_actions && !$readonly}}
        {{foreach from=$_event->_tab_actions item=_action}}
          <button class="{{$_action.class}}" 
                  data-reference_class="{{$object->_class}}"
                  data-reference_id="{{$object->_id}}"
                  data-ex_class_event_id="{{$_event->_id}}"
                  onclick="ExObject.executeTabAction(this, '{{$_action.callback}}', 'form-tab-{{$_event->_guid}}')">
            {{tr}}{{$_action.title}}{{/tr}}
          </button>
        {{/foreach}}
      {{/if}}
    </div>
  {{/foreach}}
{{/foreach}}