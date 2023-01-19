{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=groups value=$requirements_group|@array_keys}}
<table class="main">
  <tr>
      {{* Group tabs *}}
      {{if $groups|@count > 1}}
        <td style="vertical-align: top; width: 160px">
          <ul id="tabs-{{$tab}}-group" class="control_tabs_vertical small">
              {{foreach from=$groups item=group}}
                  {{assign var=group value=" "|explode:$group}}
                  {{assign var=group value="-"|implode:$group}}
                <li>
                  <a href="#content-{{$tab}}-group-{{$group}}" id="a_{{$tab}}_{{$group}}">
                      {{tr}}Requirements-group-{{$group}}{{/tr}}
                  </a>
                </li>
              {{/foreach}}
          </ul>
        </td>
      {{/if}}

      {{* content for group *}}
    <td>
        {{foreach from=$requirements_group key=group item=requirements_section}}
          {{* Reset count errors *}}
          {{assign var=current_errors value=0}}
            {{assign var=group value=" "|explode:$group}}
            {{assign var=group value="-"|implode:$group}}

          <div id="content-{{$tab}}-group-{{$group}}" {{if $groups|@count > 1}}style="display:none;"{{/if}}>
            <table class="tbl">

                {{*  section *}}
              <tr>
                <th class="title me-text-align-center">{{tr}}Requirements-section-name{{/tr}}</th>
                <th class="title me-text-align-center">{{tr}}Requirements-section-value{{/tr}}</th>
              </tr>

              {{foreach from=$requirements_section key=section item=requirements}}
                  {{* Title section *}}
                  {{if $requirements_section|@count > 1}}
                    <tr>
                      <th colspan="2" class="category me-text-align-center me-font-weight-bold">
                          {{tr}}{{if $section === "undefined"}}Requirements-title-{{/if}}{{$section}}{{/tr}}
                      </th>
                    </tr>
                  {{/if}}

                  {{foreach from=$requirements item=requirement}}
                    <tr>

                        {{* description of requirement *}}
                      <td>
                          {{$requirement.description}}
                      </td>

                        {{* Value / expected *}}
                      <td class="{{if $requirement.check}}ok{{else}}error config{{/if}}">
                          {{if $requirement.check}}
                              {{$requirement.actual}}
                          {{else}}
                              {{if $requirement.actual === null}}
                                  {{tr}}common-No data{{/tr}} -
                              {{elseif !$requirement.actual}}
                                0 -
                              {{else}}
                                  {{$requirement.actual}} -
                              {{/if}}
                            ( {{tr}}CModuleCheck-msg-value wanted{{/tr}} : {{$requirement.expected}} )
                          {{/if}}
                      </td>
                    </tr>

                      {{* Add errors *}}
                      {{if !$requirement.check}}
                          {{assign var=current_errors value=$current_errors+1}}
                      {{/if}}
                  {{/foreach}}
              {{/foreach}}
            </table>
          </div>

            <script>
              requirements.updateTitleStatus('{{$tab}}','{{$group}}', {{$current_errors}});
            </script>
        {{/foreach}}
    </td>
  </tr>
</table>