{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $resources_capabilities}}
  <tr>
    <th class="title me-text-align-center" style="width: 20%;">{{tr}}CFHIRResources{{/tr}}</th>
    <th class="title me-text-align-center" style="width: 30%;">{{tr}}FHIR.interaction|pl{{/tr}}</th>
    <th class="title me-text-align-center">{{tr}}FHIR.profile|pl{{/tr}}</th>
  </tr>
  {{foreach from=$resources_capabilities item=capabilities}}
    <tr>
      <th>{{$capabilities->getType()}}</th>

      <td>
        <ul>
          {{foreach from=$capabilities->getInteractions() item=interaction}}
            <li>{{$interaction}}</li>
          {{/foreach}}
        </ul>
      </td>

      <td>
        <p>{{$capabilities->getProfile()}}</p>
        <ul>
          {{foreach from=$capabilities->getSupportedProfiles() item=profile}}
            <li>{{$profile}}</li>
          {{/foreach}}
        </ul>
      </td>
    </tr>
  {{/foreach}}
{{else}}
  <tr>
    <td>
      <div class="info">
        {{tr}}CCapabilities-msg-none to show{{/tr}}
      </div>
    </td>
  </tr>
{{/if}}
