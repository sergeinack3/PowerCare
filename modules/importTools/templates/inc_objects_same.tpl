{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs_class_same');
    var classes = {{$classes|@json}};
    var counts = {{$counts|@json}};
    for (var i = 0; i < classes.length; i++) {
      Control.Tabs.setTabCount('same-' + classes[i], counts['same'][classes[i]]);
    }
  });
</script>

<ul class="control_tabs" id="tabs_class_same">
  {{foreach from=$regression.same key=_class item=_diff}}
    <li><a href="#same-{{$_class}}">{{tr}}{{$_class}}{{/tr}}</a></li>
  {{/foreach}}
</ul>

{{foreach from=$regression.same key=_class item=_diff}}
  <div id="same-{{$_class}}" style="display: none">
    <table class="tbl">
      <tr>
        <th class="narrow"> {{tr}}CIdSante400{{/tr}}</th>
        <th style="width: 50%;">{{tr}}mod-importTools-db1-id{{/tr}}</th>
        <th style="width: 50%;">{{tr}}mod-importTools-db2-id{{/tr}}</th>
      </tr>
      {{if !$_diff.DB1 && !$_diff.DB2}}
        <tr>
          <td class="empty" colspan="3">{{tr}}mod-importTools-none{{/tr}} {{tr}}{{$_class}}{{/tr}} {{tr}}mod-importTools-same-value{{/tr}}</td>
        </tr>
      {{else}}
        {{foreach from=$_diff.DB1 key=_id400 item=_id}}
        <tr>
          <td>{{$_id400}}</td>
          <td align="center">{{$_id}}</td>
          <td align="center">{{$_diff.DB2.$_id400}}</td>
        </tr>
        {{/foreach}}
      {{/if}}
    </table>
  </div>
{{/foreach}}