{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $actor->_id}}
  {{assign var=mod_name value=$actor->_ref_module->mod_name}}
  
  <script type="text/javascript">  
    Main.add(function () {
      tabs = Control.Tabs.create('tabs-{{$actor->_guid}}', false,  {
          afterChange: function(newContainer){
            switch (newContainer.id) {
              case "formats_available_{{$actor->_guid}}" :
                InteropActor.refreshFormatsAvailable('{{$actor->_guid}}');
                break;
              case "exchanges_sources_{{$actor->_guid}}" :
                InteropActor.refreshExchangesSources('{{$actor->_guid}}');
                break;
              case "tags_{{$actor->_guid}}" :
                InteropActor.refreshTags('{{$actor->_guid}}');
                break;
              {{if $actor->_ref_object_configs}}  
              case "actor_config_{{$actor->_id}}" :
                InteropActor.refreshConfigObjectValues('{{$actor->_id}}', '{{$actor->_ref_object_configs->_guid}}');
                break;
              {{/if}}
              case "actor_config_contextuelle_{{$actor->_id}}" :
                InteropActor.refreshConfigsReceiver('{{$actor->_guid}}');
                break;
              case "eai_transformations_{{$actor->_guid}}" :
                InteropActor.refreshEAITransformations('{{$actor->_guid}}');
                break;
            }
          }
      });
    });
  </script>
  
  <table class="main">
    <tr>
      <td>
        <ul id="tabs-{{$actor->_guid}}" class="control_tabs">
          <li>
            <a href="#formats_available_{{$actor->_guid}}">{{tr}}{{$actor->_parent_class}}_formats-available{{/tr}}</a></li>
          <li>
            <a href="#exchanges_sources_{{$actor->_guid}}">{{tr}}{{$actor->_parent_class}}_exchanges-sources{{/tr}}</a></li>
          <li>
            <a href="#tags_{{$actor->_guid}}">{{tr}}{{$actor->_parent_class}}_tags{{/tr}}</a></li>
          {{if $actor->_ref_object_configs}}
          <li>
            <a href="#actor_config_{{$actor->_id}}">{{tr}}{{$actor->_parent_class}}_config{{/tr}}</a></li>
          {{/if}}
          <!--  <li>
            <a href="#actor_config_contextuelle_{{$actor->_id}}">{{tr}}{{$actor->_parent_class}}_config{{/tr}} (New)</a>
          </li> -->
          <li>
            <a href="#eai_transformations_{{$actor->_guid}}">{{tr}}mod-eai-tab-vw_transformations{{/tr}}</a>
          </li>
        </ul>

        <div id="formats_available_{{$actor->_guid}}" style="display:none"></div>
        
        <div id="exchanges_sources_{{$actor->_guid}}" style="display:none"></div>
        
        <div id="tags_{{$actor->_guid}}" style="display:none"></div>
        
        {{if $actor->_ref_object_configs}}
          <div id="actor_config_{{$actor->_id}}" style="display: none;"></div>
        {{/if}}

        <div id="actor_config_contextuelle_{{$actor->_id}}" style="display: none;"></div>

        <div id="eai_transformations_{{$actor->_guid}}" style="display:none"></div>
      </td>
    </tr>
  </table>
{{/if}}
