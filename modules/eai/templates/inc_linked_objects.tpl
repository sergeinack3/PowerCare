{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="system" script="object_selector"}}

<script type="text/javascript">
  ObjectSelector.init = function() {
    this.sForm      = "linkObjectFrm";
    this.sId        = "object_id";
    this.sClass     = "object_class";
    this.onlyclass  = "true";
    this.pop();
  }

  Main.add(function() {
    $(getForm("linkObjectFrm").object_class).makeAutocomplete({width: "300px"});
  });
</script>

<table class="form">
  {{if $linked_objects}}
    <tr>
      <th class="category">{{tr}}CObjectToInteropSender{{/tr}}</th>
    </tr>
    {{foreach from=$linked_objects key=_class item=_objects}}
      <tr>
        <td>
          <fieldset>
            <legend>{{tr}}{{$_class}}{{/tr}} - {{$_class}}</legend>
            <table class="form">
              {{foreach from=$_objects item=_object}}
                <tr>
                  <td>
                    <form name="delFrm_{{$_object->_guid}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete : InteropActor.refreshLinkedObjects.curry('{{$actor->_guid}}')});">
                      {{mb_class object=$_object}}
                      {{mb_key object=$_object}}
                      <input type="hidden" name="del" value="1"/>
                      <label for="btnDel_{{$_object->_guid}}">{{$_object->_ref_object->_view}}</label>
                      <button id="btnDel_{{$_object->_guid}}" class="trash notext" type="button" onclick="this.form.onsubmit();">{{tr}}Delete{{/tr}}</button>
                    </form>
                  </td>
                </tr>
              {{/foreach}}
            </table>
          </fieldset>
        </td>
      </tr>
    {{/foreach}}
  {{/if}}

  <tr>
    <th class="category">{{tr}}CObjectToInteropSender-title-create{{/tr}}</th>
  </tr>
  <tr>
    <td>
      <form name="linkObjectFrm" action="?" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete : InteropActor.refreshLinkedObjects.curry('{{$actor->_guid}}')});">
        {{mb_class object=$linked_object}}
        {{mb_key object=$linked_object}}
        <input type="hidden" name="sender_class" value="{{$linked_object->sender_class}}"/>
        <input type="hidden" name="sender_id" value="{{$linked_object->sender_id}}"/>
        <input type="hidden" name="object_id" onchange="this.form.onsubmit();" value="0"/>


        <table class="form">
          <tr>
            <th>{{mb_label object=$linked_object field="object_class"}}</th>
            <td>
              <select name="object_class" style="width:300px;">
                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                {{foreach from=$classes item=class}}
                  <option value="{{$class|getShortName}}">{{$class|getShortName}} - {{tr}}{{$class|getShortName}}{{/tr}}</option>
                {{/foreach}}
              </select>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$linked_object field="object_id"}}</th>
            <td>
              <button class="add notext" type="button" onclick="ObjectSelector.init();">{{tr}}Add{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>