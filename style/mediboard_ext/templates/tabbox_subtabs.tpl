{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $subTabs}}
  <script>
    Main.add(function() {
      Control.Tabs.GroupedTabs.initialize('control_grouped_tabs', $$('.{{$containerClass|html_entity_decode}}')[0]);
    });
  </script>

  <ul class="control_tabs" id="control_grouped_tabs">
    {{foreach from=$subTabs item=subTab}}
      <li>
          <span class="subtab {{if $tab == $subTab}}active{{/if}}" id="{{$subTab}}" data-m="{{$m}}"
                data-tab="{{$subTab}}" {{if $tab == $subTab}}data-get="{{$subTabData}}"{{/if}}>
            {{tr}}mod-{{$m}}-tab-{{$subTab}}{{/tr}}
          </span>
      </li>
    {{/foreach}}
  </ul>
{{/if}}
