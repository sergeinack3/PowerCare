{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("eai-tools-tab", true);
  });
</script>

<ul class="control_tabs" id="eai-tools-tab">
{{foreach from=$tools key=_tool_class item=_tools}}
  <li>
    <a href="#tools-{{$_tool_class}}">{{tr}}CEAI-tools-{{$_tool_class}}{{/tr}}</a>
  </li>
{{/foreach}}
</ul>

{{foreach from=$tools key=_tool_class item=_tools}}
  <div id="tools-{{$_tool_class}}" style="display: none;">
    {{foreach from=$_tools item=_tool}}
      <table class="main tbl">
        <tr>
          <th colspan="2" class="category">{{tr}}CEAI-tools-{{$_tool_class}}-{{$_tool}}{{/tr}}</th>
        </tr>
      </table>
      <table class="main layout">
        <tr>
          <td style="width: 220px">
            {{mb_include module=eai template="inc_tool_`$_tool_class`_`$_tool`"}}
          </td>
          <td id="tools-{{$_tool_class}}-{{$_tool}}" colspan="4">&nbsp;</td>
        </tr>
      </table>
    {{/foreach}}
  </div>
{{/foreach}}
