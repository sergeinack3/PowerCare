{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    Control.Tabs.create('tabs-exchanges-sources-{{$actor->_guid}}');
  });
</script>

{{if !$actor->_ref_msg_supported_family}}
  <div class="small-warning">{{tr}}CMessageSupported.none{{/tr}}</div>
{{else}}

  <div>
    <ul id="tabs-exchanges-sources-{{$actor->_guid}}" class="control_tabs small">
      {{foreach from=$actor->_ref_msg_supported_family item=_msg_supported}}
        <li>
          <a href="#exchanges_sources_{{$actor->_guid}}_{{$_msg_supported}}">
            <span title="{{tr}}{{$_msg_supported}}-desc{{/tr}}" style="white-space: normal">
              {{tr}}{{$_msg_supported}}{{/tr}}
            </span>
          </a>
        </li>
      {{/foreach}}
    </ul>

    {{foreach from=$actor->_ref_msg_supported_family item=_msg_supported}}
      <div id="exchanges_sources_{{$actor->_guid}}_{{$_msg_supported}}" style="display:none;">
        {{mb_include module=system template=inc_config_exchange_source source=$actor->_ref_exchanges_sources.$_msg_supported}}
      </div>
    {{/foreach}}
  </div>
{{/if}}