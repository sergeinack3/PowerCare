{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$ressources|@count}}
  <div class="small-warning">
    {{tr}}CRessourceCab.none_for_cabinet{{/tr}}
  </div>

  {{mb_return}}
{{/if}}

<script>
  Main.add(function() {
    PlageRessource.tabs = Control.Tabs.create("ressources_tabs", true, {afterChange: function(container) {
        var ressource_cab_id = container.id.split("_")[1];
        PlageRessource.viewPlanning(ressource_cab_id);
      }
    });
  });
</script>

<ul id="ressources_tabs" class="control_tabs me-align-auto">
  {{foreach from=$ressources item=_ressource}}
  <li>
    <a href="#planning_{{$_ressource->_id}}">
      <span class="mediuser" style="border-left-color: #{{$_ressource->color}}">{{$_ressource}}</span>
      <div id="planning_{{$_ressource->_id}}" style="display: none;" class="me-no-display"></div>
    </a>
  </li>
  {{/foreach}}
  <li class="me-tabs-buttons">
    <button type="button" class="new"
            onclick="PlageRessource.edit(null, PlageRessource.getCurrentRessourceId());">{{tr}}CPlageRessourceCab-new{{/tr}}</button>
  </li>
</ul>

<div id="planning_ressources"></div>