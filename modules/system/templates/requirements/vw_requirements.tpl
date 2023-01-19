{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="modal-container-requirements">
    {{mb_default var="tpl_error" value=false}}

  <!-- errors -->
    {{if $tpl_error}}
      <div class="small-warning">
          {{$tpl_error}}
      </div>
        {{mb_return}}
    {{/if}}


    {{mb_script module=system script=requirements ajax=1}}

  <script>
    Main.add(function () {
        {{* main Tabs *}}
      Control.Tabs.create('tabs-requirements');

        {{* groups Tabs *}}
        {{foreach from=$tabs item=tab}}
        {{if $tab !== "description"}}
      Control.Tabs.create('tabs-{{$tab}}-group');
        {{/if}}
        {{/foreach}}
    });
  </script>

    {{* Selector context *}}
  <div class="me-margin-top-18">
    <table class="main">
      <tr>
        <td>
          <form>
              {{me_form_field label=common-Context}}
                <select name='group_id' onchange="requirements.changeGroup(this.form, '{{$mod_name}}')">
                  {{foreach from=$resume key=group_name item=data}}
                    <option value="{{$data.group_id}}" {{if $data.group_id === $actual_group}}selected="selected"{{/if}}>{{$group_name}}</option>
                  {{/foreach}}
                </select>
              {{/me_form_field}}
          </form>
        </td>
      </tr>
    </table>
  </div>

    {{* Resume *}}
  <div class="me-margin-top-8">
    <table class="tbl">
      <tr>
        <th colspan="4" class="title">{{tr}}Requirements-resume{{/tr}}</th>
      </tr>

      <tr>
        <th class="section" style="width: 40%">{{tr}}CGroups{{/tr}}</th>
        <th class="section">errors</th>
      </tr>

      <tr>
        <td colspan="3">
          <div style="overflow-y: scroll; max-height: 150px">
            <table style="width: 100%">
              {{foreach from=$resume key=establishment_name item=data}}
                <tr>
                  <td style="width: 40%">
                    <i class="fas fa-cogs me-margin-right-6" style="color: {{if $data.errors == 0}}green{{else}}red{{/if}}"></i>
                    {{$establishment_name}}
                  </td>
                  <td class="me-padding-left-8">{{$data.errors}} / {{$data.total}}</td>
                </tr>
              {{/foreach}}
            </table>
          </div>
        </td>
      </tr>
    </table>
  </div>


    {{* content *}}
  <div class="me-padding-0 me-margin-top-15">
      {{* Main tabs *}}
      {{if $tabs|@count > 1}}
        <ul id="tabs-requirements" class="control_tabs" style="margin-top: 0">
            {{foreach from=$tabs item=tab}}
                {{assign var=tab value=" "|explode:$tab}}
                {{assign var=tab value="-"|implode:$tab}}
              <li>
                <a href="#tab-{{$tab}}" class="" id="a_{{$tab}}">
                    {{tr}}Requirements-tab-{{$tab}}{{/tr}}
                </a>
              </li>
            {{/foreach}}
        </ul>
      {{/if}}

      {{* main tabs content *}}
      {{foreach from=$requirements_tabs key=tab item=requirements_group}}
        <div id="tab-{{$tab}}" {{if $tabs|@count > 1}}style="display: none"{{/if}}>
            {{assign var=tab value=" "|explode:$tab}}
            {{assign var=tab value="-"|implode:$tab}}
            {{mb_include module="system" template="requirements/inc_modal_requirements"}}
        </div>
      {{/foreach}}

      {{if $description}}
        <div id="tab-description" style="display: none">
          <table class="main">
            <tr>
              <td>
                <div class="markdown">
                    {{$description->render()|markdown}}
                </div>
              </td>
            </tr>
          </table>
        </div>
      {{/if}}
  </div>
</div>
