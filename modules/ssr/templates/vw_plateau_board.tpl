{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning}}
{{mb_script module=ssr script=planification}}

<script>
  Main.add(function() {
    Planification.current_m = "{{$m}}";
    Planification.showWeek(null, 'plateau');
    {{if $plateaux|@count}}
      tabs = Control.Tabs.create('tabs-plateaux', true);
      tabs.activeLink.onmousedown();
    {{/if}}
  });
  
  PlanningEvent.onMouseOver = function(event) {
    var matches = event.className.match(/CEvenementSSR-([0-9]+)/);
    if (matches) {
      ObjectTooltip.createEx(event, matches[0]);
    }
  };

  Planification.onCompleteShowWeek = function() {
    tabs.activeLink.onmousedown();
  };

  PlateauxIds = {{$plateaux_ids|@json}};
</script>

<div id="week-changer" style="height: 30px; margin: 0 100px" class="me-margin-bottom-8"></div>

<ul id="tabs-plateaux" class="control_tabs">
  {{foreach from=$plateaux item=_plateau}}
    <li>
      <a href="#{{$_plateau->_guid}}" onmousedown="PlanningEquipement.showMany(PlateauxIds['{{$_plateau->_id}}']);">
        {{$_plateau}}
      </a>
    </li>
  {{/foreach}}
</ul>

{{foreach from=$plateaux item=_plateau}}
  <div id="{{$_plateau->_guid}}" style="display: none;">
    {{foreach from=$_plateau->_back.equipements item=_equipement}}
    <div id="planning-equipement-{{$_equipement->_id}}" style="margin: 0 5px; float: left; width: 400px; height: 295px;">
      {{$_equipement}}
    </div>
    {{/foreach}}
  </div>
{{/foreach}}