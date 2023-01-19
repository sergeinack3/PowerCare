{{*
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=import script=import_campaign ajax=true}}

<script>
  Main.add(function() {
    Control.Tabs.create(
      'tab-list-classes', false, {
        afterChange: ImportCampaign.loadObjectTab
      }
    )
  });
</script>

<div class="import-layout">
  <div class="import-width-fixed">
    <ul id="tab-list-classes" class="control_tabs_vertical">
      {{foreach from=$classes item=_class}}
        <li>
          <a href="#tab-{{$_class.class_name}}">
            {{tr}}{{$_class.class_name}}{{/tr}}
            {{if 'count'|array_key_exists:$_class}}
              ({{$_class.count|number_format:0:',':' '}})
            {{/if}}
          </a>
        </li>
      {{/foreach}}
    </ul>
  </div>

  <div>
    {{foreach from=$classes item=_class}}
      <div id="tab-{{$_class.class_name}}" style="display: none;" data-classe="{{$_class.class_name}}" data-campaign="{{$campaign->_id}}"
           data-show_errors="{{$show_errors}}">
      </div>
    {{/foreach}}
  </div>
</div>