{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{tr}}mutex_tester-title{{/tr}}</h2>

<div class="small-info">
  {{tr}}mutex_tester-info1{{/tr}}
  <br />
  {{tr}}mutex_tester-info2{{/tr}}
</div>

<script type="text/javascript">

function test(action) {
  var url = new Url("dPdeveloppement", "httpreq_test_mutex");
  url.addParam("action", action);
  url.requestUpdate(action);
}

function testMulti(count) {
  count = count || 5;

  var target = $("test-multi");
  target.update("");

  while (count-- > 0) {
    var url = new Url("dPdeveloppement", "httpreq_test_mutex_multi");
    url.addParam("i", count);
    url.requestJSON(function(data) {
      target.insert(data.driver+" - Requête N°"+data.i+" : "+data.time+" s<br />");
    });
  }
}

</script>

<table class="tbl">
  <tr>
    <th>{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>

  {{foreach from=$actions item=_action}}
  <tr>
    <td><button class="tick" onclick="test('{{$_action}}')" >{{tr}}test_mutex-{{$_action}}{{/tr}}</button></td>
    <td id="{{$_action}}"></td>
  </tr>
  {{/foreach}}

  <tr>
    <td><button class="tick" onclick="testMulti()">Test multi</button></td>
    <td id="test-multi"></td>
  </tr>
</table>