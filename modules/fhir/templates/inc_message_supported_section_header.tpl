{{*
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_message_supported value=0}}
{{foreach from=$_messages_supported item=_message}}
    {{if $_message->_id}}
        {{assign var=_message_supported value=$_message}}
    {{/if}}
{{/foreach}}

{{if !$_message_supported}}
    {{assign var=_message_supported value=$_messages_supported|@first}}
{{/if}}

{{assign var=_category_name value=$_message_supported->transaction}}
<script>
  updateVersions = function(element, container_id, family, category_id) {
    var element_option = element.childElements()[element.selectedIndex]
    element_option.onchange();
    new Url('eai', 'messageRefreshSectionVersion')
      .addFormData(element.form)
      .addParam('family', family)
      .requestUpdate(container_id, {
        onComplete: function() {
          element.form.onsubmit()
          var messages = document.getElementsByClassName(category_id)
          for (let i = 0; i < messages.length; i++) {
            // update le message
            var message = messages[i]
            new Url('eai', 'refreshMessageSupported')
              .addFormData(element.form)
              .addParam('family', family)
              .requestUpdate(message.id)
          }
        }
      })
  };

  updateDelegated = function (element, delegated_type) {
    let delegated = $V(element)
    if (isEmpty(delegated)) {
      return;
    }

    const form = getForm(element.getAttribute('form'));
    let delegated_values = {};
    delegated_values[delegated_type] = delegated;
    const object_ids = getMessageSupportedIds(form);
    if (isEmpty(object_ids)) {
      return [];
    }

    new Url('fhir', 'updateDelegatedValues')
      .addFormData(form)
      .addParam('delegated_values', JSON.stringify(delegated_values))
      .addParam('message_supported_ids[]', object_ids)
      .requestUpdate("systemMsg")
  };

  getMessageSupportedIds = function (form) {
    const category_uid = form.getAttribute('data-category_uid');
    const group_class = 'actor_message_supported_' + category_uid;
    const elements = document.getElementsByClassName(group_class);
    let object_ids = [];
    for (let tr of elements) {
      const message_id = $V(tr.childElements()[0].select("form")[0].elements.message_supported_id);
      if (message_id) {
        object_ids.push(message_id);
      }
    }

    return object_ids;
  };

  updateAllDelegated = function (form) {
    const object_ids = getMessageSupportedIds(form);
    const category_uid = form.getAttribute('data-category_uid');
    const family_name = form.getAttribute('data-family');

    var delegated_form_name = "messages_managed_" + family_name + "_" + category_uid;
    var delegated_form = getForm(delegated_form_name);
    var table = delegated_form.parentElement.parentElement.parentElement;

    const delegated_values = {};
    var delegated_inputs = table.querySelectorAll(`select[form=${delegated_form_name}]`);
    for (delegated_input of delegated_inputs) {
      const value = $V(delegated_input);
      if (value) {
        delegated_values[delegated_input.getAttribute('data-type')] = value;
      }
    }
    if (isEmpty(object_ids) || isEmpty(delegated_values)) {
      return;
    }

    new Url('fhir', 'updateDelegatedValues')
      .addFormData(form)
      .addParam('delegated_values', JSON.stringify(delegated_values))
      .addParam('message_supported_ids[]', object_ids)
      .requestUpdate("systemMsg")
  };

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

    return onSubmitFormAjax(form, {
      'onComplete': function () {
        setTimeout(() => updateAllDelegated(form), 1000)
      }
    });
  }
</script>

<tr>
  <th style="text-align: left;" class="section" colspan="4">
    {{* checkAll *}}
    <button class="fa fa-check notext" onclick="checkAll('{{$_family_name}}', '{{$category_uid}}')"></button>

    {{* Resource FHIR *}}
    {{assign var=resource value='Ox\Interop\Fhir\CExchangeFHIR::getResourceFromCanonical'|static_call:$_category_name}}
    {{assign var=resource_type value=$resource|const:'RESOURCE_TYPE'}}
    {{assign var=resource_type_profiled value=$resource|const:'PROFILE_TYPE'}}

    <span title="{{$_category_name}}">
        {{$resource_type}} {{if $resource_type_profiled}}[{{$resource_type_profiled}}]{{/if}}
    </span>

    {{assign var=form_name value=" "|str_replace:"_":"messages_managed $_family_name $category_uid"}}
    <form name="{{$form_name}}" method="post"
          onsubmit="InteropActor.updateMessageSupported(this)"
          data-category_uid="{{$category_uid}}"
          data-family="{{$_family_name}}"
          data-category="{{$_category_name}}">
      <input type="hidden" name="object_id" value="{{$_message_supported->object_id}}"/>
      <input type="hidden" name="object_class" value="{{$_message_supported->object_class}}"/>
      <input type="hidden" name="message" value="{{$_message_supported->message}}"/>
      <input type="hidden" name="transaction" value="{{$_category_name}}"/>
      <input type="hidden" name="old_transaction" value="{{$_category_name}}"/>
      <input type="hidden" name="profil" value="{{$_family_name}}"/>
      <input type="hidden" name="category_uid" value="{{$category_uid}}"/>

      {{* version *}}
      <span id="container_version_{{$_family_name}}_{{$category_uid}}">
        {{if $_families->_versions_category}}
          {{mb_include module=eai template=inc_message_supported_section_version}}
        {{/if}}
      </span>
    </form>
  </th>
</tr>

<tr>
  {{* Delegated mapper object *}}
  <th colspan="4">
      <div style="display: inline-flex; justify-content: space-around" class="me-margin-6">
          {{mb_include module="fhir" template="inc_choice_object_delegated" delegated_type='mapper' delegated_form="$form_name"}}

          {{* Delegated searcher object *}}
          {{mb_include module="fhir" template="inc_choice_object_delegated" delegated_type='searcher' delegated_form="$form_name"}}

          {{* Delegated handle object *}}
          {{mb_include module="fhir" template="inc_choice_object_delegated" delegated_type='handle' delegated_form="$form_name"}}
      </div>
  </th>
</tr>
