{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-sejours', true).activeLink.onmouseup();
  });
</script>

<ul id="tabs-sejours" class="control_tabs">
  {{foreach from=$counts key=mode item=_count}}
  <li>
    <a {{if !$_count}} class="empty" {{/if}} href="#board-sejours-{{$mode}}" onmouseup="BoardSejours.updateTab('{{$mode}}');">
      {{tr}}ssr-board-sejours-{{$mode}}{{/tr}}
      <small>({{$_count}})</small>
    </a>
  </li>
  {{/foreach}}
</ul>

<label>
  <input name="hide_noevents" type="checkbox" {{if $hide_noevents}} checked="true" {{/if}} onclick="BoardSejours.update(this.checked)" />
  {{tr}}ssr-hide_noevents{{/tr}}
</label>

{{foreach from=$counts key=mode item=_count}}
<div id="board-sejours-{{$mode}}" style="display: none;"></div>
{{/foreach}}
