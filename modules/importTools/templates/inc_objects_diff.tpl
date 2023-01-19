{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs_class_diff');
    var classes = {{$classes|@json}};
    var counts = {{$counts|@json}};
    for (var i = 0; i < classes.length; i++) {
      Control.Tabs.setTabCount('diff-' + classes[i], counts['diff'][classes[i]]);
    }
  });
</script>

<ul class="control_tabs" id="tabs_class_diff">
  {{foreach from=$regression.diff key=_class item=_diff}}
    <li><a href="#diff-{{$_class}}">{{tr}}{{$_class}}{{/tr}}</a></li>
  {{/foreach}}
</ul>

{{foreach from=$regression.diff key=_class item=_diff}}
  <div id="diff-{{$_class}}" style="display: none">
    <table class="main tbl">
      <tr>
        <th class="title" colspan="3">{{tr}}{{$_class}}{{/tr}} ({{$_class}})</th>
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
          <td class="empty" colspan="3">
            {{tr}}mod-importTools-no-difference{{/tr}} {{tr}}{{$_class}}{{/tr}}.
          </td>
        </tr>
      {{else}}

        {{foreach from=$_diff.DB1 key=_id400 item=_values}}
          {{if $_values && $_diff.DB2.$_id400}}
            <tr>
              <td>{{$_id400}}</td>
              <td class="text">
                <ul>
                  {{foreach from=$_values key=_name item=_value}}
                    {{if $_value || (array_key_exists($_name, $_diff.DB2.$_id400) && $_diff.DB2.$_id400.$_name)}}
                      <li>
                        {{if array_key_exists($_name, $_diff.DB2.$_id400) && $_value == $_diff.DB2.$_id400.$_name}}
                        <span style="color: #007f00">
                    {{else}}
                          <span style="color: #ff0100">
                    {{/if}}
                            {{$_name}} : {{$_value}}
                    </span>
                      </li>
                    {{/if}}
                  {{/foreach}}
                </ul>
              </td>
              <td class="text">
                <ul>
                  {{foreach from=$_diff.DB2.$_id400 key=_name item=_value}}
                    {{if $_value || (array_key_exists($_name, $_diff.DB1.$_id400) && $_diff.DB1.$_id400.$_name)}}
                      <li>
                        {{if array_key_exists($_name, $_diff.DB1.$_id400) && $_value == $_diff.DB1.$_id400.$_name}}
                          <span style="color: #007f00">
                        {{else}}
                          <span style="color: #ff0100">
                        {{/if}}
                      {{$_name}} : {{$_value}}
                        </span>
                      </li>
                    {{/if}}
                  {{/foreach}}
                </ul>
              </td>
            </tr>
          {{/if}}
        {{/foreach}}
      {{/if}}
    </table>
  </div>
{{/foreach}}