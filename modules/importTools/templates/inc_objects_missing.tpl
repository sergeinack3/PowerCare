{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs_class_miss');
    var classes = {{$classes|@json}};
    var counts = {{$counts|@json}};
    for (var i = 0; i < classes.length; i++) {
      Control.Tabs.setTabCount('miss-' + classes[i], counts['miss'][classes[i]]);
    }
  });
</script>

<ul class="control_tabs" id="tabs_class_miss">
  {{foreach from=$regression.miss key=_class item=_diff}}
    <li><a href="#miss-{{$_class}}">{{tr}}{{$_class}}{{/tr}}</a></li>
  {{/foreach}}
</ul>

{{foreach from=$regression.miss key=_class item=_diff}}
  <div id="miss-{{$_class}}" style="display: none">
    <table class="main tbl">
      <tr>
        <th class="section" colspan="4">
          {{tr}}{{$_class}}{{/tr}} ({{$_class}})
        </th>
      </tr>
      <tr>
        <th class="narrow">
          {{tr}}CIdSante400{{/tr}}
        </th>
        <th>
          {{tr}}mod-importTools-db1{{/tr}}
        </th>
        <th>
          {{tr}}mod-importTools-db2{{/tr}}
        </th>
      </tr>

      {{if !$_diff.DB1 && !$_diff.DB2}}
        <tr>
          <td colspan="3" class="empty">
            {{tr}}mod-importTools-missing-none{{/tr}} {{tr}}{{$_class}}{{/tr}}.
          </td>
        </tr>

      {{else}}

        {{foreach from=$_diff.DB1 key=_id400 item=_values}}
          {{if !$_values || !$_diff.DB2.$_id400}}
            <tr>
              <td>
                {{$_id400}}
              </td>
              <td class="text" style="width: 50%;">
                {{if !$_values}}
                  <span style="color: #ff0100">{{tr}}mod-importTools-object-missing{{/tr}}</span>
                {{else}}
                  <span style="color: #007f00">{{tr}}mod-importTools-object-ok{{/tr}}</span>
                  <button class="fa fa-caret-down notext" type="button" onclick="RegressionTester.showDatas('{{$_class}}-DB1-{{$_id400}}')">{{tr}}mod-importTools-object-hints{{/tr}}</button>
                  <ul id="{{$_class}}-DB1-{{$_id400}}" style="display: none">
                    {{foreach from=$_values key=_name item=_value}}
                      {{if $_value}}
                        <li>{{$_name}} : {{$_value}}</li>
                      {{/if}}
                    {{/foreach}}
                  </ul>
                {{/if}}
              </td>
              <td class="text" style="width: 50%;">
                {{if !$_diff.DB2.$_id400}}
                  <span style="color: #ff0100">{{tr}}mod-importTools-object-missing{{/tr}}</span>
                {{else}}
                  <span style="color: #007f00">{{tr}}mod-importTools-object-ok{{/tr}}</span>
                  <button class="fa fa-caret-down notext" type="button" onclick="RegressionTester.showDatas('{{$_class}}-DB2-{{$_id400}}')">{{tr}}mod-importTools-object-hints{{/tr}}</button>

                  <table id="{{$_class}}-DB2-{{$_id400}}" class="tbl" style="display: none">
                    {{foreach from=$_diff.DB2.$_id400 key=_name item=_value}}
                      {{if $_value}}
                        <tr>
                          <td class="narrow">{{$_name}}</td>
                          <td>{{$_value}}</td>
                        </tr>
                      {{/if}}
                    {{/foreach}}
                  </table>
                {{/if}}
              </td>
            </tr>
          {{/if}}
        {{/foreach}}

      {{/if}}
    </table>
  </div>
{{/foreach}}