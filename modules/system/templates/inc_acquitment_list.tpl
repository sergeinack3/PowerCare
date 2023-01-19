{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl main">
  <tr>
    <td colspan="2">{{mb_include module=system template=inc_pagination total=$total change_page="Message.acquittalsSwitchPage" current=$limit step=10}}</td>
  </tr>

  <tr>
    <th>{{tr}}Date{{/tr}}</th>
    <th>{{tr}}Utilisateur{{/tr}}</th>
  </tr>

  {{foreach from=$acquittals item=_acquittal}}
    <tr>
      <td>{{$_acquittal->date|date_format:$conf.datetime}}</td>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_acquittal->_ref_user}}</td>
    </tr>
  {{foreachelse}}
    <tr><td colspan="2" class="empty">{{tr}}CAcquittementMsgSystem.none{{/tr}}</td></tr>
  {{/foreach}}
</table>