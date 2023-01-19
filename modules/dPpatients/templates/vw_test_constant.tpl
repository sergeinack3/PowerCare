{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  {{assign var=length value=4}}
  {{foreach from=$tests key=name item=_tests}}
    <tr>
      <th colspan="{{$length}}" class="title">{{$name}}</th>
    </tr>
    <tr>
      <th>Test</th>
      <th>expected</th>
      <th>result</th>
    </tr>
    {{foreach from=$_tests item=_test}}
      <tr>
        <td style="background-color: rgba(10,109,207,0.58)">{{$_test.text}}</td>
        <td class="narrow highlight">{{$_test.expected}}</td>
        {{if $_test.res}}
          <td class="ok">{{$_test.result}}</td>
        {{else}}
          <td class="error">{{$_test.result}}</td>
        {{/if}}
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>