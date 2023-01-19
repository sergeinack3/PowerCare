{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module="eai" template="inc_form_session_receiver" onComplete="TestFHIR.showFHIRResources()"}}
{{if !$cn_receiver_guid}}
  {{mb_return}}
{{/if}}

{{* Format de la request *}}
<form name="request_options_fhir">
  <input type="hidden" name="use_actor_capabilities" value="{{$use_actor_capabilities}}">

  <table class="form">
    <tr>
      <td colspan="2">
        <button type="button"
                style="float: left; margin-left: 5px;"
                onclick="TestFHIR.viewMessagesSupported('{{$cn_receiver_guid}}', 'CExchangeFHIR')">
          <img src="modules/fhir/images/icon.png" width="16">
          {{tr}}CExchangeFHIR{{/tr}}
        </button>

        <button type="button"
                class="fas {{if $use_actor_capabilities}}fa-toggle-on{{else}}fa-toggle-off{{/if}}"
                style="float: left; margin-left: 5px;"
                onclick="changeToggle(this);"
                id="toggle_actor_capabilities">
          <span class="me-color-black-high-emphasis" style="color: #000000">{{tr}}CFHIR-msg-Show receiver capabilities{{/tr}}</span>
        </button>

        {{if $use_actor_capabilities}}

          <button type="button"
                  class="fas fa-toggle-off"
                  style="float: left; margin-left: 5px;"
                  onclick="showContainer(this, 'container_resources_not_activated');">
            <span class="me-color-black-high-emphasis" style="color: #000000">{{tr}}CFHIR-msg-Show receiver capabilities not activated{{/tr}}</span>
          </button>

          <button type="button"
                  class="fas fa-toggle-off"
                  style="float: left; margin-left: 5px;"
                  onclick="showContainer(this, 'container_resources_not_supported')">
            <span class="me-color-black-high-emphasis" style="color: #000000">{{tr}}CFHIR-msg-Show receiver capabilities not supported{{/tr}}</span>
          </button>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th><label for="response_type" title="Format de la réponse">Format de la réponse</label></th>
      <td>
        <label for="response_type">fhir+json</label>
        <input type="radio" name="response_type" value="json" />
        <label for="response_type_xml">fhir+xml</label>
        <input type="radio" name="response_type" value="xml" checked/>
      </td>
    </tr>
  </table>
</form>
