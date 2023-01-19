{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changePageField = function (page) {
    var url = new Url('dPdeveloppement', 'ajax_display_errors_details');
    url.addParam('start', page);
    url.addParam('field_id', {{$field->_id}});
    url.addParam('container', '{{$container}}');
    url.requestUpdate('{{$container}}');
  }
</script>

<table class="main tbl">
  <tr>
    <td colspan="2">
      {{mb_include module=system template=inc_pagination total=$total current=$start step=$step change_page="changePageField"}}
    </td>
  </tr>

  <tr>
    <th>{{mb_title class=CRefError field=missing_id}}</th>
    <th>{{mb_title class=CRefError field=count_use}}</th>
  </tr>

  {{foreach from=$errors item=_error}}
    <tr>
      <td>{{mb_value object=$_error field=missing_id}}</td>
      <td>{{mb_value object=$_error field=count_use}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="2">
        {{tr}}CRefError.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>