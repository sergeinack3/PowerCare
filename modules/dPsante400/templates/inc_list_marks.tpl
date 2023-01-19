{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  changePage = function (page) {
    var url = new Url('{{$m}}', '{{$action}}', 'tab');
    url.addParam('pagination_start', page);
    url.redirect();
  }

</script>

{{mb_include module=system template=inc_pagination change_page=changePage
total=$pagination.total current=$pagination.start step=$pagination.step}}

<table class="tbl me-no-align">
  <tr>
    <th>{{mb_title class=CTriggerMark field=trigger_number}}</th>
    <th>{{mb_title class=CTriggerMark field=trigger_class}}</th>
    <th colspan="2">{{mb_title class=CTriggerMark field=when}}</th>
    <th>{{mb_title class=CTriggerMark field=mark}}</th>
    <th colspan="2" class="narrow">{{mb_title class=CTriggerMark field=done}}</th>
  </tr>

  {{assign var=href value="?m=dPsante400&$actionType=$action&dialog=$dialog"}}

  {{foreach from=$marks item=_mark}}
    <tr {{if $_mark->_id == $mark->_id}}class="selected"{{/if}}>
      <td>
        <a class="button edit compact" href="{{$href}}&mark_id={{$_mark->_id}}">
          {{mb_value object=$_mark field=trigger_number}}
        </a>
      </td>
      <td>{{tr}}{{mb_value object=$_mark field=trigger_class}}{{/tr}}</td>
      <td>{{$_mark->when|iso_datetime}}</td>
      <td>{{mb_value object=$_mark field=when format=relative}}</td>
      <td><tt>{{mb_value object=$_mark field=mark}}</tt></td>
      <td>{{mb_value object=$_mark field=done}}</td>
      <td class="button">
        <button class="search compact" onclick="Mouvements.retry('{{$_mark->trigger_class}}', '{{$_mark->trigger_number}}');">
          {{tr}}Retry{{/tr}}
        </button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CTriggerMark.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
