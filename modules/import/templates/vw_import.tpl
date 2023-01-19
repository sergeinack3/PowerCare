{{*
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=a_value value=ajax_vw_import}}

<script>
  Main.add(function () {
    Control.Tabs.create('import-tabs', true, {
      afterChange: function (container) {
        var url = new Url('{{$module}}', '{{$a_value}}');
        url.addParam('type', container.get('type'));
        url.addParam('import_campaign_id', $V($('import-campaign-select')));
        url.addParam('import_type', '{{$import_type}}');
        url.requestUpdate(container);
      }
    });
  });
</script>

<div class="campaign-select">
    {{mb_include module=import template=inc_vw_import_campaign_select}}
</div>

<div class="import-layout">
  <div class="import-width-fixed">
    <ul class="control_tabs_vertical" id="import-tabs">
        {{foreach from=$import_order key=_type item=_count}}
          <li>
            <a {{if $_count === '0'}}class="empty"{{/if}} href="#tab-{{$_type}}">
                {{tr}}mod-import-type-{{$_type}}{{/tr}}
                {{if $_count > 0 && $_count !== true}}({{$_count|number_format:0:',':' '}}){{/if}}
            </a>
          </li>
        {{/foreach}}
    </ul>
  </div>

  <div>
      {{foreach from=$import_order key=_type item=_count}}
        <div style="display: none;" id="tab-{{$_type}}" data-type="{{$_type}}"></div>
      {{/foreach}}
  </div>
</div>
