{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs_missing_class');
    Control.Tabs.setTabCount('missing_in_1', {{$comp.Base1|@count}});
    Control.Tabs.setTabCount('missing_in_2', {{$comp.Base2|@count}});
  });
</script>

<div>
  <ul class="control_tabs" id="tabs_missing_class">
    <li><a href="#missing_in_1">{{tr}}mod-importTools-missing_in_1{{/tr}}</a></li>
    <li><a href="#missing_in_2">{{tr}}mod-importTools-missing_in_2{{/tr}}</a></li>
  </ul>

  <div id="missing_in_1" style="display: none">
      <table class="main tbl">
        <tr>
          <th>{{tr}}mod-importTools-db1{{/tr}} : {{tr}}CIdSante400{{/tr}}</th>
        </tr>
        {{if !$comp.Base1}}
          <tr>
            <td class="empty" align="center">{{tr}}mod-importTools-missing_in_1_none{{/tr}}.</td>
          </tr>
        {{else}}
          {{foreach from=$comp.Base1 item=_id400}}
            <tr>
              <td align="center">{{$_id400}}</td>
            </tr>
          {{/foreach}}
        {{/if}}
      </table>
  </div>

  <div id="missing_in_2" style="display: none">
    <table class="main tbl">
      <tr>
        <th>{{tr}}mod-importTools-db2{{/tr}} : {{tr}}CIdSante400{{/tr}}</th>
      </tr>
      {{if !$comp.Base2}}
        <tr>
          <td class="empty" align="center">{{tr}}mod-importTools-missing_in_2_none{{/tr}}.</td>
        </tr>
      {{else}}
        {{foreach from=$comp.Base2 item=_id400}}
          <tr>
            <td align="center">{{$_id400}}</td>
          </tr>
        {{/foreach}}
      {{/if}}
    </table>
  </div>
</div>



