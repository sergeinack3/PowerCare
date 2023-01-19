{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $dialog}}
  {{assign var=destform value="m=$m&dialog=1&a=$action"}}
{{else}}
  {{assign var=destform value="m=$m&tab=$tab"}}
{{/if}}

<script>
  var to = [];

  addTo = function (to_id, element_to_update, label) {
    //exist ?
    var index = to.indexOf(to_id);
    if (index >= 0 || to_id == "") {
      $V(element_to_update, "");
      return;
    }

    //insert in array
    to.push(to_id);

    //insert value in form
    var oform = getForm("EditUserMessage");
    $V(oform.to_list, to.join("|"));
    $V(oform.to, to_id);

    //clear the input
    var prat = label ? label : $V(element_to_update);
    $V(element_to_update, "");

    //list update
    var list = $('listUser');
    list.insert(DOM.li({}, "<button type='button' id=\"listUser-"+to_id+"\" onclick=\"removeTo('"+to_id+"');\" class='cancel'>"+prat+"</button>"));
  };

  removeTo = function(to_id) {
    var list = $('listUser');
    var position = to.indexOf(to_id);

    //exist
    if (position >= 0) {
      to.splice(position,1);
      $("listUser-"+to_id).remove();
    }
  };


  Main.add(function() {
    Control.Tabs.create('tabs-usermessage', true);
    {{if count($usermessage->_ref_users_to) && !$usermessage->date_sent}}
      var element_to_update = getForm("EditUserMessage")._to_autocomplete_view;
      {{foreach from=$usermessage->_ref_users_to item=_user}}
        addTo('{{$_user->_id}}', element_to_update, '{{$_user->_view}}');
      {{/foreach}}
    {{/if}}
  });
</script>

<ul id="tabs-usermessage" class="control_tabs">
  <li><a href="#tab_mail">{{if $usermessage->_id}}
        {{tr}}CUserMessage-title-modify{{/tr}} '{{$usermessage}}'
      {{else}}
        {{tr}}CUserMessage-title-create{{/tr}}
      {{/if}}
      </a>
  </li>
  <li>
    <a href="#historique" {{if !$historique|@count}}class="empty"{{/if}}>Historique ({{$historique|@count}})</a>
  </li>
</ul>

<div id="tab_mail" style="display: none;">
  <form name="EditUserMessage" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="dosql" value="do_usermessage_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="usermessage_id" value="{{$usermessage->_id}}" />
    <input type="hidden" name="postRedirect" value="{{$destform}}" />
    <input type="hidden" name="in_reply_to" value="{{$usermessage->in_reply_to}}" />
    <input type="hidden" name="grouped" value="{{$usermessage->grouped}}" />
    {{if !$usermessage->date_sent}}
      {{mb_field object=$usermessage field=date_sent hidden=true}}
    {{else}}
      {{mb_field object=$usermessage field=archived hidden=true}}
      {{mb_field object=$usermessage field=starred hidden=true}}
    {{/if}}
    <table class="form">
      <tr>
        <th class="narrow">{{mb_label object=$usermessage field=from}}</th>
        <td>
          {{mb_field object=$usermessage field=from hidden=1}}
          <div class="mediuser" style="border-color: #{{$usermessage->_ref_user_from->_ref_function->color}};">
            {{$usermessage->_ref_user_from}}
          </div>
        </td>
      </tr>
    
      {{if $usermessage->date_sent}}
        <tr>
          <th>{{mb_label object=$usermessage field=date_sent}}</th>
          <td>{{mb_value object=$usermessage field=date_sent}} ({{mb_value object=$usermessage field=date_sent format=relative}})</td>
        </tr>
      {{/if}}
    
      <tr>
        <th>{{mb_label object=$usermessage field=to}}</th>
        <td>
          {{if $usermessage->date_sent}}
            {{foreach from=$usermessage->_ref_users_to item=user_to}}
            <div class="mediuser" style="border-color: #{{$user_to->_ref_function->color}};">
              {{$user_to}}
            </div>
            {{/foreach}}
          {{else}}
            {{mb_field object=$usermessage field=to hidden=true}}
            <input type="hidden" name="to_list" value=""/>
            <ul id="listUser" style="padding:0;">
              <li>
                <input type="text" name="_to_autocomplete_view" style="width: 16em;" class="autocomplete" value=""/>
              </li>
            </ul>
            <script>
              Main.add(function(){
                var form = getForm("EditUserMessage");
                var element = form.elements._to_autocomplete_view;
                var url = new Url("system", "ajax_seek_autocomplete");
                url.addParam("object_class", "CMediusers");
                url.addParam("input_field", element.name);
                url.addParam("show_view", true);
                url.autoComplete(element, null, {
                  minChars: 3,
                  method: "get",
                  select: "view",
                  dropdown: true,
                  afterUpdateElement: function(field,selected){
                    var id = selected.getAttribute("id").split("-")[2];
                    addTo(id, element);
                  }
                });
              });
            </script>
          {{/if}}
        </td>
      </tr>
    
      {{if $usermessage->date_read}}
      <tr>
        <th>{{mb_label object=$usermessage field=date_read}}</th>
        <td>{{mb_value object=$usermessage field=date_read}} ({{mb_value object=$usermessage field=date_read format=relative}})</td>
      </tr>
      {{/if}}
    
      {{if $usermessage->archived}}
      <tr>
        <th>{{mb_label object=$usermessage field=archived}}</th>
        <td><strong>{{mb_value object=$usermessage field=archived}}</strong></td>
      </tr>
      {{/if}}
    
      {{if $usermessage->starred}}
      <tr>
        <th>{{mb_label object=$usermessage field=starred}}</th>
        <td><strong>{{mb_value object=$usermessage field=starred}}</strong></td>
      </tr>
      {{/if}}
    
      <tr>
        <th>{{mb_label object=$usermessage field=subject}}</th>
        <td>
          {{if $usermessage->date_sent}}
            {{mb_value object=$usermessage field=subject}}
          {{else}}
            {{mb_field object=$usermessage field=subject size=80}}
          {{/if}}
        </td>
      </tr>
    
      <tr>
        <td colspan="2" style="height: 300px">{{mb_field object=$usermessage field=source id="htmlarea"}}</td>
      </tr>
    
      {{if !$usermessage->date_sent}}
      <tr>
        <td colspan="2" style="text-align: center;">
          <button type="submit" class="send" onclick="$V(this.form.date_sent, 'now');">{{tr}}Send{{/tr}}</button>
          <button type="submit" class="submit">{{tr}}Save{{/tr}} {{tr}}Draft{{/tr}}</button>
        </td>
      </tr>
      {{elseif $usermessage->to == $app->user_id}}
      <tr>
        <td colspan="2" style="text-align: center;">
          <button type="button" onclick="window.parent.Control.Modal.close(); window.parent.UserMessage.createWithSubject({{$usermessage->_ref_user_from->_id}}, 'Re: {{$usermessage->_clean_subject}}'); ">
            {{me_img src="usermessage.png" icon="mail" class="me-primary" alt="message"}}
            {{tr}}CUserMessage.answer{{/tr}}
          </button>
          {{if !$usermessage->starred}}
            {{if $usermessage->archived}}
            <button type="submit" class="cancel" onclick="$V(this.form.archived, '0');">{{tr}}Unarchive{{/tr}}</button>
            {{else}}
            <button type="submit" class="change" onclick="$V(this.form.archived, '1');">{{tr}}Archive{{/tr}}</button>
            {{/if}}
          {{/if}}
                
          {{if !$usermessage->archived}}
            {{if $usermessage->starred}}
            <button type="submit" class="cancel" onclick="$V(this.form.starred, '0');">{{tr}}Unstar{{/tr}}</button>
            {{else}}
            <button type="submit" class="new" onclick="$V(this.form.starred, '1');">{{tr}}Star{{/tr}}</button>
            {{/if}}
          {{/if}}
        </td>
      </tr>
      {{/if}}
    </table>
  </form>
</div>

<div id="historique" style="display: none;">
  <table class="tbl">
    <tr>
      <th style="width: 20%">{{mb_label object=$usermessage field=from}}</th>
      <th style="width: 20%;">{{mb_label object=$usermessage field=to}}</th>
      <th>{{mb_label object=$usermessage field=subject}}</th>
    </tr>
    {{foreach from=$historique item=_usermessage}}
      <tr>
        <td>
          {{$_usermessage->_ref_user_from}}
        </td>
        <td>
          {{foreach from=$_usermessage->_ref_users_to item=_user_to}}
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user_to}}
          {{/foreach}}
        </td>
        <td>
          <a href="#1" onmouseover="ObjectTooltip.createDOM(this, 'usermessage_{{$_usermessage->_id}}')">{{$_usermessage->subject}}</a>
          <div style="display: none" id="usermessage_{{$_usermessage->_id}}">
            <table class="tbl">
              <tr>
                <th class="category">Contenu du message</th>
              </tr>
              <tr>
                <td>
                  {{$_usermessage->source|smarty:nodefaults}}
                </td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="3">{{tr}}CUserMessage.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</div>

