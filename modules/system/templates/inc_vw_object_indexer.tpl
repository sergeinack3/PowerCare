
{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm('search-object_indexer');
    form.onsubmit();
  });
</script>

<form name="search-object_indexer" method="get" onsubmit="return Url.update(this, 'object_indexer-result');">
  <input type="hidden" name="m" value="{{$m}}">
  <input type="hidden" name="a" value="ajax_search_object_indexer">
  <input type="hidden" name="index_name" value="{{$index_name}}">

  <table class="main form">
    <tr>
      <th colspan="2">
        <h1>{{$index_name|replace:"index-":""}}</h1>
      </th>
    </tr>
    <tr>
      <td class="button">
        <input type="search" name="tokens" placeholder="&mdash; {{tr}}Search{{/tr}}">
        <button type="submit" class="search notext">{{tr}}Search{{/tr}}</button>
        <button type="submit" class="erase notext" onclick="ObjectIndexer.clearTiming(); this.form.tokens.clear()">{{tr}}Clear{{/tr}}</button>
        <span style="float: right;" id="result_infos"></span>
      </td>
    </tr>
  </table>
</form>

<div id="object_indexer-result"></div>