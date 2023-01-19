{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=eai script=exchange_data_format ajax=1}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-profiles', true);
    {{foreach from=$resources key=profile_type item=data_resources}}
      Control.Tabs.create('tabs-tests_{{$profile_type}}_resources', true);
    {{/foreach}}
  });

  changeToggle = function(element) {
    var form = getForm('request_options_fhir')
    var input = form.elements.use_actor_capabilities
    var value = parseInt(input.value)
    if (value) {
      element.setAttribute('class', 'fas fa-toggle-off');
    }
    else {
      element.setAttribute('class', 'fas fa-toggle-on');
    }
    input.value = value ? 0 : 1

    TestFHIR.showFHIRResources();
  }

  showContainer = function (button, container_id) {
    var container = document.getElementById(container_id)
    if (!container) {
      return
    }

    var state = container.getAttribute('data-state')
    if (state === 'off') {
      container.style.display = 'table'
      container.setAttribute('data-state', 'on')
      button.classList.remove('fa-toggle-off')
      button.classList.add('fa-toggle-on')
    } else {
      container.style.display = 'none'
      container.setAttribute('data-state', 'off')
      button.classList.add('fa-toggle-off')
      button.classList.remove('fa-toggle-on')
    }
  }
</script>

{{mb_include module=fhir template="Tests/inc_headers_crud_operations"}}
{{if !$cn_receiver_guid}}
  {{mb_return}}
{{/if}}

{{if $use_actor_capabilities}}
  {{mb_include module=fhir template="Tests/inc_std_crud_operations"}}

  <table class="tbl" id="container_resources_not_activated" data-state="off" style="display: none">
    {{mb_include module=fhir template="Tests/inc_show_resources_capabilities" resources_capabilities=$resources_not_actived}}
  </table>

  <table class="tbl" id="container_resources_not_supported" data-state="off" style="display: none">
    {{mb_include module=fhir template="Tests/inc_show_resources_capabilities" resources_capabilities=$resources_not_supported}}
  </table>

{{else}}
  {{mb_include module=fhir template="Tests/inc_std_crud_operations"}}
{{/if}}

<div id="result_crud_operations"> </div>
