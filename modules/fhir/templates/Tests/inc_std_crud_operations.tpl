{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table style="width: 100%">
  <tr>
    <td>
      <ul id="tabs-profiles" class="control_tabs">
        {{foreach from=$resources key=profile_type item=data_resources}}
          <li>
            <a href="#tab-{{$profile_type}}" id="a_content_{{$profile_type}}">
              {{tr}}{{$data_resources.profile|getShortName}}{{/tr}}
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>
  </tr>

  <tr>
    <td>
      {{foreach from=$resources key=profile_type item=data_resources}}
        <div id="tab-{{$profile_type}}" class="tab-container" style="display: none">
          <table class="form">
            <tr>
              <td style="vertical-align: top; width: 100px">
                <ul id="tabs-tests_{{$profile_type}}_resources" class="control_tabs_vertical">
                  {{foreach from=$data_resources.resources item=capabilities}}
                    <li><a href="#{{$profile_type}}-{{$capabilities->getProfile()}}">{{tr}}CFHIRResource{{$capabilities->getType()}}{{/tr}}</a></li>
                  {{/foreach}}
                </ul>
              </td>

              <td style="vertical-align: top;">
                {{foreach from=$data_resources.resources item=capabilities}}
                  {{assign var="resource_profile" value=$capabilities->getProfile()}}
                  <div id="{{$profile_type}}-{{$resource_profile}}" style="display: none">
                    {{mb_include template="inc_vw_crud_operation"}}
                  </div>
                {{/foreach}}
              </td>
            </tr>
          </table>
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>
