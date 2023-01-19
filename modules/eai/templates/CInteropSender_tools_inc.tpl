{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=mssante_active value="mssante"|module_active}}

{{if $actor->_id}}
    {{assign var=mod_name value=$actor->_ref_module->mod_name}}
    <script type="text/javascript">
        Main.add(function () {
            tabs = Control.Tabs.create('tabs-{{$actor->_guid}}', false, {
                afterChange: function (newContainer) {
                    switch (newContainer.id) {
                        case "exchanges_sources_{{$actor->_guid}}" :
                InteropActor.refreshExchangesSources('{{$actor->_guid}}');
                break;
              case "formats_available_{{$actor->_guid}}" :
                InteropActor.refreshFormatsAvailable('{{$actor->_guid}}');
                break;
              case "configs_formats_{{$actor->_guid}}" :      
                InteropActor.refreshConfigsFormats('{{$actor->_guid}}');
                break;
              case "configs_contextuelle_{{$actor->_guid}}" :
                InteropActor.refreshConfigsSender('{{$actor->_guid}}');
                break;
              case "tags_{{$actor->_guid}}" :
                InteropActor.refreshTags('{{$actor->_guid}}');
                break;
              case "linked_objects_{{$actor->_guid}}" :
                InteropActor.refreshLinkedObjects('{{$actor->_guid}}');
                break;
              case "routes_{{$actor->_guid}}" :
                InteropActor.refreshRoutes('{{$actor->_guid}}');
                break;
              case "transformations_{{$actor->_guid}}" :
                InteropActor.refreshTransformations('{{$actor->_guid}}');
                break;
            }
          }
      });
    });
  </script>

  {{assign var=count_routes value=$actor->_count.routes_sender}}

  <table class="main">
    <tr>
      <td>
        <ul id="tabs-{{$actor->_guid}}" class="control_tabs">
            {{if !$actor|instanceof:'Ox\Interop\Webservices\CSenderSOAP' || ($mssante_active && (!$actor|instanceof:'Ox\Mediboard\Mssante\CSenderMSSante' && !$actor|instanceof:'Ox\Interop\Webservices\CSenderSOAP'))}}
                <li>
                    <a href="#exchanges_sources_{{$actor->_guid}}">{{tr}}{{$actor->_parent_class}}_exchanges-sources{{/tr}}</a>
                </li>
            {{/if}}
          <li>
            <a href="#formats_available_{{$actor->_guid}}">{{tr}}{{$actor->_parent_class}}_formats-available{{/tr}}</a></li>
          <li>
            <a href="#configs_formats_{{$actor->_guid}}">{{tr}}{{$actor->_class}}_configs-formats{{/tr}}</a></li>
          <li>
            <!-- <li>
            <a href="#configs_contextuelle_{{$actor->_guid}}">{{tr}}{{$actor->_class}}_configs-formats{{/tr}} (New)</a></li>
          <li> -->
            <a href="#tags_{{$actor->_guid}}">{{tr}}{{$actor->_parent_class}}_tags{{/tr}}</a></li>  
          <li>
            <a href="#linked_objects_{{$actor->_guid}}">{{tr}}CObjectToInteropSender{{/tr}}</a>
          </li>
          <li>
            <a href="#transformations_{{$actor->_guid}}">{{tr}}CTransformationRule{{/tr}}</a>
          </li>
          <li>
            <a class="{{if $count_routes == 0}}empty{{else}}wrong{{/if}}" href="#routes_{{$actor->_guid}}">
              {{tr}}{{$actor->_class}}_routes{{/tr}} {{if $count_routes > 0}}({{$count_routes}}){{/if}}
            </a>
          </li>
          <li>
            <a href="#actions_{{$actor->_guid}}">{{tr}}{{$actor->_class}}_actions{{/tr}}</a></li>
        </ul>

          {{if !$actor|instanceof:'Ox\Interop\Webservices\CSenderSOAP' || ($mssante_active && (!$actor|instanceof:'Ox\Mediboard\Mssante\CSenderMSSante' && !$actor|instanceof:'Ox\Interop\Webservices\CSenderSOAP'))}}
              <div id="exchanges_sources_{{$actor->_guid}}" style="display:none;"></div>
          {{/if}}
        
        <div id="formats_available_{{$actor->_guid}}" style="display:none"></div>
        
        <div id="configs_formats_{{$actor->_guid}}" style="display:none"></div>

        <div id="configs_contextuelle_{{$actor->_guid}}" style="display:none"></div>

        <div id="tags_{{$actor->_guid}}" style="display:none"></div>
        
        <div id="linked_objects_{{$actor->_guid}}" style="display: none"></div>

        <div id="transformations_{{$actor->_guid}}" style="display: none"></div>

        <div id="routes_{{$actor->_guid}}" style="display: none"></div>
        
        <div id="actions_{{$actor->_guid}}" style="display:none">
          {{mb_include module=$mod_name template="`$actor->_class`_actions_inc" ignore_errors=true}}
        </div>
      </td>
    </tr>
  </table>
{{/if}}
