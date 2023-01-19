{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$actor->_id}}
  <div class="small-error">{{tr}}CInteropActor-msg-None actor{{/tr}}</div>
  {{mb_return}}
{{/if}}

<script>
  {{if $actor->_ref_msg_supported_family}}
  document.getElementById("menu_exchange").setAttribute("class", "special");
  {{/if}}

  {{if $source}}
  document.getElementById("menu_source").setAttribute("class", "special active");
  {{/if}}
</script>

{{mb_include module=eai template=inc_summary_actor}}

{{if !$actor->_ref_msg_supported_family}}
  <div class="small-warning">{{tr}}CMessageSupported.none{{/tr}}</div>
{{else}}
  {{foreach from=$actor->_ref_msg_supported_family item=_msg_supported}}
    {{unique_id var=uid numeric=true}}
    <form name="create_source_{{$actor->_guid}}_{{$uid}}" action="?m={{$m}}" method="post"
          onsubmit="return onSubmitFormAjax(this, {onComplete: function() {
            InteropActor.refreshSourceReceiver('{{$actor->_guid}}');
          } })">
      <fieldset>
        <legend>
          {{tr}}{{$_msg_supported}}{{/tr}}
        </legend>
        <fieldset>
          <legend>{{tr}}CSourceLPR.type{{/tr}}</legend>
          <table class="form">
            {{assign var=source_actor value=$actor->_ref_exchanges_sources.$_msg_supported}}
            {{assign var=type_class value='Ox\Mediboard\System\CExchangeSource'|static:"typeToClass"}}
            <tr>
              <td>
                {{foreach from=$types_source key=type item=_source}}
                  <label>
                    <input type="radio" name="type_receiver_{{$uid}}" value="{{$type}}"
                      {{if $source_actor && $source_actor->_id}}
                        {{assign var=key_value value='CSourceSOAP'|array_search:$type_class}}
                        {{if $key_value && $key_value == $type}} checked{{/if}}
                      {{/if}}
                           onchange="InteropActor.chooseTypeSource('{{$actor->_guid}}', '{{$uid}}', '{{$_source}}');"/> {{tr}}{{$_source}}{{/tr}}
                  </label>
                {{/foreach}}
              </td>
            </tr>
          </table>
        </fieldset>
        <fieldset>
          <legend>{{tr}}config-source{{/tr}}</legend>
            <div id="exchanges_sources_{{$actor->_guid}}_{{$_msg_supported}}" style="display:block;">

              {{if $source_actor && $source_actor->_id}}
                {{mb_key object=$source_actor}}
                {{mb_class object=$source_actor}}

                <script>
                  var inputs = document.getElementsByName("type_receiver_" + {{$uid}});

                  for (var i=0; i<inputs.length; i++) {
                    {{if $source_actor && $source_actor->_id}}
                  {{/if}}
                  }
                </script>
              {{else}}
                {{mb_class object=$source_reference}}
              {{/if}}

              <table class="form">
                <tr>
                  <th>{{mb_label object=$source_actor field="name"}}</th>
                  <td><input type="text" name="name" value="{{$source_actor->name}}" size="50"/></td>
                </tr>
                <tr>
                  <th>{{mb_label object=$source_actor  field="host"}}</th>
                  <td>{{mb_field object=$source_actor  field="host"}}</td>
                </tr>
                <tr>
                  <th>{{mb_label object=$actor field="role"}}</th>
                  <td>{{mb_field object=$actor field="role" typeEnum="radio"}}</td>
                </tr>
                <tr>
                  <th></th>
                  <td class="button">
                    {{if $source_actor && $source_actor->_id}}
                      <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                      <button class="trash" type="button" onclick="confirmDeletion(this.form,
                              { ajax: 1, typeName: '', objName: '{{$source_actor->_view}}'},
                              { onComplete: (function() {
                              InteropActor.refreshSourceReceiver('{{$actor->_guid}}');
                              }).bind(this.form)})">
                        {{tr}}Delete{{/tr}}
                      </button>
                    {{else}}
                      <button class="submit" disabled type="submit" name="submit_button">{{tr}}Create{{/tr}}</button>
                    {{/if}}
                  </td>
                </tr>
              </table>
            </div>
        </fieldset>
      </fieldset>
    </form>
  {{/foreach}}
{{/if}}

<button type="button" class="fa fa-chevron-circle-right" style="margin-top: 10px; float: right;">
  <a href="#configs_receiver" style="text-decoration: none;">{{tr}}Next{{/tr}}</a>
</button>