{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=default value=$objects.default}}
{{assign var=objects value=$objects.objects}}

<script type="text/javascript">
  refreshConfigObjectValues = function(object_id, object_configs_guid) {
    var url = new Url("system", "ajax_config_object_values");
    url.addParam("object_id", object_id);
    url.addParam("object_configs_guid", object_configs_guid);
    url.requestUpdate("objects-"+object_id);
  };
	  
  Main.add(function(){
    Control.Tabs.create('tabs-config-objects', true).activeLink.up().onmousedown();
  });
</script>

<table>
  <tr>
    <td style="vertical-align: top;">
      <ul id="tabs-config-objects" class="control_tabs_vertical">
        <li onmousedown="refreshConfigObjectValues('{{$default->_id}}', '{{$default->_ref_object_configs->_guid}}');">
          <a href="#objects-{{$default->_id}}">
            {{tr}}Default{{/tr}}
          </a>
        </li>
        {{foreach from=$objects item=_object}}
          <li onmousedown="refreshConfigObjectValues('{{$_object->_id}}', '{{$_object->_ref_object_configs->_guid}}');">
            <a href="#objects-{{$_object->_id}}">
              {{$_object->_view}}
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>
    <td style="vertical-align: top;">
      <div id="objects-{{$default->_id}}" style="display: none;">
    
      </div>
      {{foreach from=$objects item=_object}}
      <div id="objects-{{$_object->_id}}" style="display: none;">
        
      </div>
      {{/foreach}}
    </td>
  </tr>
</table>   