{{*
 * @package Mediboard\API_tiers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create("menu-api-{{$_tab}}", true);
  });
</script>

<table class="main layout">
  <tr>
    <td class="narrow">
      <ul id="menu-api-{{$_tab}}" class="control_tabs_vertical">
        {{foreach from=$configuration.$_tab key=_source_name item=_source}}
          <li><a href="#config-{{$_source_name}}">{{tr}}{{$_source_name}}{{/tr}}</a></li>
        {{/foreach}}
      </ul>
    </td>
    {{foreach from=$configuration.$_tab key=_source_name item=_source}}
      <td id="config-{{$_source_name}}">
        <div>
          {{mb_include module=system template=inc_config_exchange_source source=$_source}}
        </div>
      </td>
    {{/foreach}}
  </tr>
</table>