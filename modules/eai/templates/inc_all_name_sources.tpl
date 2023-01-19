{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-configure', true);
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  {{foreach from=$sources key=_name_source item=_source}}
    <li><a class="empty" href="#source_{{$_name_source}}">{{tr}}{{$_name_source}}{{/tr}}</a></li>
  {{/foreach}}
</ul>

{{foreach from=$sources key=_name_source item=_source}}
  <div id="source_{{$_name_source}}" style="display: none;">
    {{mb_include module=$_source->_ref_module->mod_name template="`$_name_source`_inc_config" source=$_source readonly=false
    callback="Source.refreshAll('$_name_source')"}}
  </div>
{{/foreach}}