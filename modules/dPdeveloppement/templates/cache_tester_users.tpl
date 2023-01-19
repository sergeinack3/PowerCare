{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{tr}}cache_tester-info1{{/tr}}
  <br/>
  {{tr}}cache_tester-info2{{/tr}}
</div>

<table class="tbl">
  <tr>
    <th>{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Duration{{/tr}}</th>
  </tr>
  {{foreach from=$chrono->report key=_key item=_chrono}}
    <tr>
      <td>{{$_key}}</td>
      <td>{{math assign=total equation="x * 1000" x=$_chrono->total}} {{$total|float:2}}ms</td>
    </tr>
  {{/foreach}}
  <tr>
    <td class="button" colspan="2">
      <button type="button" class="trash" onclick="CacheTester.users(1);">
        {{tr}}Cache-remove_key{{/tr}}
      </button>
    </td>
  </tr>
</table>

<table class="tbl">
  <tr>
    <th colspan="2">{{tr}}CMediusers{{/tr}} <small>({{$mediusers|@count}})</small></th>
    <th colspan="2">{{tr}}CFunctions{{/tr}}</th>
  </tr>

  {{foreach from=$mediusers item=_mediuser}}
  <tr>
    <td class="narrow"><tt>{{$_mediuser->_guid}}</tt></td>
    <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_mediuser}}</td>
    {{assign var=_function value=$_mediuser->_ref_function}}
    <td class="narrow"><tt>{{$_function->_guid}}</tt></td>
    <td>{{mb_include module=mediusers template=inc_vw_function function=$_mediuser->_ref_function}}</td>
  </tr>
  {{foreachelse}}
  <tr><td class="empty">{{tr}}CMediuser.none{{/tr}}</td></tr>
  {{/foreach}}

</table>