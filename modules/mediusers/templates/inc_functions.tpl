{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title narrow"></th>
    <th class="title">{{tr}}CFunctions-group_id{{/tr}} - {{tr}}CFunctions{{/tr}}</th>
    <th class="title" colspan="2">
      <button type="button" class="search" style="float:right" onclick="CMediuserFunctions.displayLegend()">
        {{tr}}Legend{{/tr}}
      </button>
      {{mb_label class=CPermObject field=permission}}
    </th>
  </tr>
  {{foreach from=$groups item=_group}}
    {{mb_include module=mediusers template=inc_functions_line element=$_group.object type=false perm=$_group.perm_object}}
    {{foreach from=$_group.functions item=_secondary_function}}
      {{assign var=secondary_function value=$_secondary_function.object}}
      {{assign var=function value=$_secondary_function.object->_ref_function}}
      {{assign var=type value=$_secondary_function.type}}
      {{assign var=perm value=$_secondary_function.perm_object}}
      {{mb_include module=mediusers template=inc_functions_line element=$function}}
    {{/foreach}}
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">{{tr}}CFunctions.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  <tr>
    <td class="button" colspan="4">
      {{if $can->edit}}
        <button type="button" class="add" onclick="CMediuserFunctions.loadAddForm()"
                title="{{tr}}CMediuser-Functions add secondary function{{/tr}}">{{tr}}Add{{/tr}}</button>
      {{/if}}
    </td>
  </tr>
</table>

{{mb_include module=mediusers template=inc_functions_legend}}


{{if $can->edit}}
  <form name="add_perm_form" method="post" class="prepared"
        onsubmit="return onSubmitFormAjax(this, function() {CMediuserFunctions.refreshView();})">
    {{mb_class class=CPermObject}}
    <input type="hidden" name="permission" value="1" />
    <input type="hidden" name="user_id" value="{{$user->_id}}" />
    <input type="hidden" name="object_id" />
    <input type="hidden" name="object_class" />
  </form>

  <div id="edit_perm" style="display: none;width: 210px;">
    <form name="edit_perm_form" method="post" class="prepared"
          onsubmit="return onSubmitFormAjax(this, function() { Control.Modal.close(); CMediuserFunctions.refreshView();});">
      <input type="hidden" name="del" value="0" />
      {{mb_class class=CPermObject}}
      {{mb_key   object=$empty_perm}}
      <table class="form me-no-box-shadow">
        <tr>
          <td class="narrow button">{{mb_field object=$empty_perm field=permission}}</td>
        </tr>
        <tr>
          <td class="narrow button">
            <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
            <button class="trash" type="button"
                    onclick="CMediuserFunctions.deletePerm(this.form.perm_object_id, true);">
              {{tr}}Delete{{/tr}}
            </button>
          </td>
        </tr>
      </table>
    </form>
  </div>

  <form name="edit_fun_form" class="prepared" method="post"
        onsubmit="return onSubmitFormAjax(this, function() { CMediuserFunctions.refreshView(); });">
    {{mb_class class=CSecondaryFunction}}
    <input type="hidden" name="function_id"/>
    <input type="hidden" name="user_id" value="{{$user->_id}}" />
    <input type="hidden" name="secondary_function_id"/>
  </form>

  <form name="upgrade_fun_form" class="prepared" method="post"
      onsubmit="return onSubmitFormAjax(this, function() {getForm('downgrade_fun_form').onsubmit();});">
    {{mb_class object=$user}}
    {{mb_key   object=$user}}
    <input type="hidden" name="function_id"/>
  </form>

  <form name="downgrade_fun_form" action="#" method="post"
        onsubmit="return onSubmitFormAjax(this, function() {CMediuserFunctions.refreshView()});">
    {{mb_class class=CSecondaryFunction}}
    {{mb_field class=CSecondaryFunction field=secondary_function_id hidden=true}}
    {{if !$auto_down_pf}}
      <input type="hidden" name="del" value="1"/>
    {{/if}}
    <input type="hidden" name="user_id" value="{{$user->_id}}" />
    <input type="hidden" name="function_id" value="{{$user->function_id}}" />
  </form>
{{/if}}
