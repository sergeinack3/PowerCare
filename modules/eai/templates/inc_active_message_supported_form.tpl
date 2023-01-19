{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  if ((typeof updateMessageSupported) == 'undefined') {
    updateMessageSupported = function(form) {
      // update value version
        {{if $_families->_versions_category}}
      var family_name = form.getAttribute('data-family');
      var category_uid = form.getAttribute('data-category_uid');
      var form_version = getForm("messages_versions_" + family_name +"_" + category_uid);
      if (form_version) {
        $V(form.elements.version, $V(form_version.elements.version))
      }
        {{/if}}

      return onSubmitFormAjax(form);
    }
    }
</script>

<form class="form-message-supported-{{$_family_name}}-{{$category_uid}}"
          name="editActorMessageSupported-{{$uid}}" method="post" onsubmit="return updateMessageSupported(this);"
          data-category_uid="{{$category_uid}}"
          data-family="{{$_family_name}}"
          data-category="{{$_category_name}}">

      {{mb_key object=$_message_supported}}
      {{mb_class object=$_message_supported}}
      <input type="hidden" name="object_id" value="{{$_message_supported->object_id}}" />
      <input type="hidden" name="object_class" value="{{$_message_supported->object_class}}" />
      <input type="hidden" name="message" value="{{$_message_supported->message}}" />
      <input type="hidden" name="profil" value="{{$_family_name}}" />
      {{if $_message_supported->_id}}
        <input type="hidden" name="active" value="{{$_message_supported->active|ternary:'0':'1'}}" />
      {{else}}
        <input type="hidden" name="active" value="1" />
      {{/if}}

      <input type="hidden" name="callback"
             value="ExchangeDataFormat.fillMessageSupportedID.curry({{$uid}}, {{$category_uid}})" />

      {{if $_category_name && $_category_name != "none"}}
        <input type="hidden" name="transaction" value="{{$_category_name}}" />
      {{/if}}

      {{if $_families->_versions_category}}
        <input type="hidden" name="version" value="{{$_message_supported->version}}" />
      {{/if}}

      <a href="#1" onclick="this.up('form').onsubmit()"
         style="display: inline-block; vertical-align: middle;">
        {{if $_message_supported->active}}
          <i class="fa fa-toggle-on" style="color: #449944; font-size: large;"></i>
        {{else}}
          <i class="fa fa-toggle-off" style="font-size: large;"></i>
        {{/if}}
      </a>
    </form>
