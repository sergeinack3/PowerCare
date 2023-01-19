{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tab-planning_collectif', true, {
      afterChange: function (container) {
        var ids = object = container.id.split('-');
        TrameCollective.refreshPlanning(ids[2]);
      }
    });
    {{foreach from=$trames item=_trame}}
      ViewPort.SetAvlHeight("planning-collectif-{{$_trame->_id}}", 1.0);
      $('planning-collectif-{{$_trame->_id}}').removeClassName('y-scroll');
    {{/foreach}}
  });
</script>

{{if $trames|@count}}
  <div class="me-align-auto">
    <ul id="tab-planning_collectif" class="control_tabs me-align-auto me-box-sizing-content">
      {{foreach from=$trames item=_trame}}
        <li>
          <a href="#planning-collectif-{{$_trame->_id}}" onmousedown="TrameCollective.refreshPlanning('{{$_trame->_id}}');">
            {{mb_include module=system template=inc_vw_mbobject object=$_trame}}
          </a>
        </li>
      {{/foreach}}
      <li style="float:right;">
        <label>
          {{tr}}CPlageCollective.show_inactive{{/tr}}
          <input value="{{$show_inactive}}" type="checkbox" {{if $show_inactive}}checked{{/if}}
                 name="show_plage_inactive" onchange="TrameCollective.refreshAllPlannings($V(this));" />
        </label>
      </li>
    </ul>
  </div>
  {{foreach from=$trames item=_trame}}
    <div id="planning-collectif-{{$_trame->_id}}" style="display:none;height: 100%;" class="me-align-auto me-no-border me-padding-0"></div>
  {{/foreach}}
{{else}}
  <div class="small-info">{{tr}}CTrameSeanceCollective.none{{/tr}}</div>
{{/if}}
