{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=profile_class value=$data_resources.profile|getShortName}}
{{assign var=form_id value="form_fhir_$resource_profile"}}
{{assign var=form_id value="$form_id$profile_class"}}
<script>
  updateInput = function (form_id, element, name) {
    var form = getForm(form_id)
    var input = form.elements[name]
    if (input) {
      $V(input, $V(element))
    }
  }
</script>

<form name="{{$form_id}}" id="{{$form_id}}">
  <input type="hidden" name="resource_type" value="{{$capabilities->getType()}}">
  <input type="hidden" name="profile" value="{{$capabilities->getProfile()}}">
  <input type="hidden" name="resource_id" value="">
  <input type="hidden" name="version_id" value="">
  <input type="hidden" name="contents" value="">
  <table>
    {{foreach from=$capabilities->getInteractions() item=interaction}}
      <tr>
        {{if $interaction == "capabilities"}}
          <td colspan="2">
            <button type="button" class="fa fa-search-plus" onclick="TestFHIR.capabilityStatement();">
              {{tr}}CFHIRInteractionCapabilities{{/tr}}
            </button>
          </td>
        {{/if}}

        {{if $interaction == "read"}}
          <td class="narrow">
            <button type="button" class="fa fa-search-plus me-primary"
                    onclick="TestFHIR.crudOperations(this.form, '{{$interaction}}')">
              {{tr}}CFHIRInteractionRead{{/tr}}
            </button>
          </td>
          <td>
            <input placeholder="Resource ID" type="text" value="" onchange="updateInput('{{$form_id}}', this, 'resource_id')"/>
          </td>
        {{/if}}
        {{if $interaction == "search-type"}}
          <td colspan="2">
            <button type="button" class="fa fa-search" onclick="TestFHIR.crudOperations(this.form, '{{$interaction}}');">
              {{tr}}CFHIRInteractionSearch{{/tr}}
            </button>
          </td>
        {{/if}}
        {{if $interaction == "create"}}
          <td class="narrow">
            <button type="button" class="far fa-save"
                    onclick="TestFHIR.crudOperations(this.form, '{{$interaction}}');">
              {{tr}}CFHIRInteractionCreate{{/tr}}
            </button>
          </td>
          <td>
            <input placeholder="Object ID" type="number" value="" onchange="updateInput('{{$form_id}}', this, 'resource_id')"/>
          </td>
        {{/if}}
        {{if $interaction == "update"}}
          <td>
            <button class="far fa-edit"
                    onclick="TestFHIR.crudOperations(this.form, '{{$interaction}}');">
              {{tr}}CFHIRInteractionUpdate{{/tr}}
            </button>
          </td>
          <td>
            <input placeholder="Resource ID" type="text" value="" onchange="updateInput('{{$form_id}}', this, 'resource_id')"/>
          </td>
        {{/if}}
        {{if $interaction == "delete"}}
          <td>
            <button type="button" class="fa fa-eraser"
                    onclick="TestFHIR.crudOperations(this.form, '{{$interaction}}');">
              {{tr}}CFHIRInteractionDelete{{/tr}}
            </button>
          </td>
          <td>
            <input placeholder="Resource ID" type="text" value="" onchange="updateInput('{{$form_id}}', this, 'resource_id')"/>
          </td>
        {{/if}}
        {{if $interaction == "history-instance"}}
          <td class="narrow">
            <button type="button" class="fa fa-history"
                    onclick="TestFHIR.crudOperations(this.form, '{{$interaction}}')">
              {{tr}}CFHIRInteractionHistory{{/tr}}
            </button>
          </td>
          <td>
            <input type="text" placeholder="Resource ID" value="" onchange="updateInput('{{$form_id}}', this, 'resource_id')"/>
            <input type="number" placeholder="Version ID" value="" onchange="updateInput('{{$form_id}}', this, 'version_id')"/>
          </td>
        {{/if}}
      </tr>
    {{/foreach}}
  </table>
</form>




