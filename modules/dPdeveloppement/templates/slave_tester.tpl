{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  <div>{{tr}}slave_tester-info1{{/tr}}</div>
  <div>{{tr}}slave_tester-info2{{/tr}}</div>
  <div>{{tr}}slave_tester-info3{{/tr}}</div>
  <div>{{tr}}slave_tester-info4{{/tr}}</div>
</div>

<form name="SlaveTester" action="?" method="get">

<input type="hidden" name="m" value="{{$m}}" />
<input type="hidden" name="{{$actionType}}" value="{{$action}}" />
<input type="hidden" name="do" value="1" />

<table class="form">
  <tr>
    <th><label for="times">{{tr}}slave_tester-times{{/tr}}</th>
    <td><input name="times" value="{{$times}}" class="num notNull pos max|100 default|20" /></td>
    <th><label for="duration">{{tr}}slave_tester-duration{{/tr}}</th>
    <td><input name="duration" value="{{$duration}}" class="num notNull pos max|60 default|1" /></td>
  </tr>
  <tr>
    <td colspan="6" class="button">
      <button type="submit" class="change">{{tr}}Execute{{/tr}}</button>
    </td>
  </tr>
</table>

</form>

{{if !$do}}
  {{mb_return}}
{{/if}}


<table class="tbl">
  <tr>
    <th>{{tr}}Datetime{{/tr}}</th>
    <th>{{tr}}Datasource{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>

  {{assign var=dsn value=""}}
  {{foreach from=$reports item=_report}}
    <tr>
      <td>{{$_report.time}}</td>

      <td>
        {{if $dsn && $dsn != $_report.dsn}}
          {{$dsn}} &rarr;
        {{/if}}
        {{$_report.dsn}}
        {{assign var=dsn value=$_report.dsn}}
      </td>
      <td class="{{$_report.errno|ternary:"warning":"ok"}}">
        {{if $_report.errno || $_report.error}}
        <tt>[{{$_report.errno}}] {{$_report.error}}</tt>
        {{else}}
        <tt>{{tr}}Success{{/tr}}</tt>
        {{/if}}
      </td>
    </tr>
  {{/foreach}}
</table>