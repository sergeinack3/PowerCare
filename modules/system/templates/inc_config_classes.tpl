{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshConfigObjects = function(classname) {
    var url = new Url("system", "ajax_config_objects");
    url.addParam("classname", classname);
    url.requestUpdate("classes-"+classname);
  };

  Main.add(Control.Tabs.create.curry('tabs-config-classes', true));
</script>

<ul id="tabs-config-classes" class="control_tabs">
  {{foreach from=$classes item=_class}}
    <li onmousedown="refreshConfigObjects('{{$_class->_class}}');">
      <a href="#classes-{{$_class->_class}}">{{tr}}{{$_class->_class}}{{/tr}}</a></li>
  {{/foreach}}
</ul>

{{foreach from=$classes item=_class}}
<div id="classes-{{$_class->_class}}" style="display: none;">
  <div class="small-info">{{tr}}config-choose-classes{{/tr}}</div>
</div>
{{/foreach}}