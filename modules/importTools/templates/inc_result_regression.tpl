{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=importTools script=RegressionTester ajax=true}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs_presents_absents');
    Control.Tabs.setTabCount('objects_missing', '{{$counts.miss.all}} / {{$total}}');
    Control.Tabs.setTabCount('objects_diff', '{{$counts.diff.all}} / {{$total}}');
    Control.Tabs.setTabCount('objects_same', '{{$counts.same.all}} / {{$total}}');
  });
</script>

{{if $msg}}
  <div class="small-error">
    {{tr}}{{$msg}}{{/tr}}.
  </div>
{{else}}
  {{if $regression.missing_in_1}}
    <div class="small-warning">
      {{tr}}mod-importTools-classes{{/tr}} {{', '|implode:$regression.missing_in_1}} {{tr}}mod-importTools-present-2-not-1{{/tr}}.
    </div>
  {{/if}}

  {{if $regression.missing_in_2}}
    <div class="small-warning">
      {{tr}}mod-importTools-classes{{/tr}} {{', '|implode:$regression.missing_in_2}} {{tr}}mod-importTools-present-1-not-2{{/tr}}.
    </div>
  {{/if}}

  <div>
    <table class="tbl">
      <tr>
        <th colspan="100">{{tr}}mod-importTools-object-import-nb{{/tr}}</th>
      </tr>
      <tr>
        {{foreach from=$classes item=_class}}
          <th colspan="2">
            {{tr}}{{$_class}}{{/tr}}
            <button class="search notext" type="button" onclick="RegressionTester.compareClasses('{{$_class}}', '{{$tag}}')">
              {{tr}}mod-importTools-compare-classe{{/tr}} {{tr}}{{$_class}}{{/tr}}
            </button>
          </th>
        {{/foreach}}
      </tr>
      <tr>
        {{foreach from=$classes item=_class}}
          <th>{{tr}}mod-importTools-db1{{/tr}}</th>
          <th>{{tr}}mod-importTools-db2{{/tr}}</th>
        {{/foreach}}
      </tr>
      <tr>
        {{foreach from=$classes item=_class}}
          <td align="center">
            {{if $counts.$_class.DB1 < $counts.$_class.DB2}}
              <span style="color: #ff0100">
            {{else}}
              <span style="color: #007f00">
            {{/if}}
              {{$counts.$_class.DB1}}
            </span>
          </td>
          <td align="center">
            {{if $counts.$_class.DB1 > $counts.$_class.DB2}}
            <span style="color: #ff0100">
            {{else}}
              <span style="color: #007f00">
            {{/if}}
              {{$counts.$_class.DB2}}
            </span>
          </td>
        {{/foreach}}
      </tr>
    </table>
  </div>

  <div>
    <ul class="control_tabs" id="tabs_presents_absents">
      <li><a href="#objects_missing">{{tr}}mod-importTools-objects-missing{{/tr}}</a></li>
      <li><a href="#objects_diff">{{tr}}mod-importTools-objects-diff{{/tr}}</a></li>
      <li><a href="#objects_same">{{tr}}mod-importTools-objects-same{{/tr}}</a></li>
    </ul>

    <div id="objects_missing" style="display: none">
      {{mb_include module=importTools template=inc_objects_missing}}
    </div>

    <div id="objects_diff" style="display: none">
      {{mb_include module=importTools template=inc_objects_diff}}
    </div>

    <div id="objects_same" style="display: none">
      {{mb_include module=importTools template=inc_objects_same}}
    </div>
  </div>
{{/if}}