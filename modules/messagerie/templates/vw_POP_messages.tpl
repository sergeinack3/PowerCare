{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=messagerie script=UserEmail}}

{{if !$account_ok}}
  <div class="small-warning">
    {{tr}}CSourcePOP-error-AccountNotFound{{/tr}} (<a href="?m=mediusers&a=edit_infos">{{tr}}menu-myInfo{{/tr}}</a>)
  </div>
{{/if}}

<table class="main">
  <tr>
    <td style="vertical-align: top; " class="narrow">
      <ul id="tab-mail" class="control_tabs_vertical">
        {{foreach from=$listMails key=k item=_item}}
          {{assign var=count value=$_item|@count}}
          <li>
            <a href="#{{$k}}" style="white-space: nowrap;" {{if !$count}}class="empty"{{/if}}>{{tr}}CUserMail.{{$k}}{{/tr}} <small>({{$count}})</small> </a>
          </li>
        {{/foreach}}

      </ul>
    </td>
    <td>
      {{foreach from=$listMails key=k item=_list}}
        <table class="main tbl" id="{{$k}}" style="display: none;">
          <tr>
            <th class="title" colspan="4">{{tr}}CUserMail.{{$k}}{{/tr}} </th>
          </tr>
          <tr>
            <th>
              {{tr}}Date{{/tr}}
            </th>
            <th>
              {{tr}}Subject{{/tr}}
            </th>
            <th>
              {{tr}}From{{/tr}}
            </th>
            <th>
              {{tr}}To{{/tr}}
            </th>
          </tr>
          {{foreach from=$_list item=_msg}}
            <tr>
              <td>{{mb_value object=$_msg field=date_inbox format=relative}}</td>
              <td><a href="#{{$_msg->uid}}"  onclick="UserEmail.modalPOPOpen({{$_msg->uid}});">{{if $_msg->subject}}{{mb_include template=inc_vw_type_message subject=$_msg->subject}}<strong>{{$_msg->subject|truncate:100:"(...)"}}</strong>{{else}}{{tr}}CUserMail-no_subject{{/tr}}{{/if}}</a></td>
              <td><label title="{{$_msg->from}}">{{$_msg->_from}}</label></td>
              <td><label title="{{$_msg->to}}">{{$_msg->_to}}</label></td>
            </tr>
          {{/foreach}}
        </table>
      {{/foreach}}
    </td>
  </tr>
</table>