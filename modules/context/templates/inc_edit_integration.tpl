{{*
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    ContextualIntegration.toggleIconURL("edit-{{$integration->_guid}}");
    ContextualIntegration.iconAutocomplete("edit-{{$integration->_guid}}");
  });
</script>

<form name="edit-{{$integration->_guid}}" action="" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="context" />
  <input type="hidden" name="callback" value="ContextualIntegration.editCallback" />
  {{mb_class object=$integration}}
  {{mb_key   object=$integration}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$integration}}
    <tr>
      {{me_form_bool nb_cells=2 mb_object=$integration mb_field=active}}
        {{mb_field object=$integration field=active}}
     {{/me_form_bool}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$integration mb_field=title}}
        {{mb_field object=$integration field=title}}
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$integration mb_field=url}}
        {{mb_field object=$integration field=url size=70}}
      {{/me_form_field}}
    </tr>
    <tr>
      <td colspan="2">
        {{assign var=patterns value='Ox\Mediboard\Context\CContextualIntegration'|static:"patterns"}}
        <fieldset>
          <legend>{{tr}}CContextualIntegrationLocation.value_url{{/tr}}</legend>
          <ul>
            {{foreach from=$patterns item=_pattern}}
              <li>
                <a href="#1" onclick="ContextualIntegration.insertPattern('{{$_pattern}}', this.up('form').url)">
                  {{tr}}CContextualIntegration.pattern.{{$_pattern}}{{/tr}}
                </a>
              </li>
            {{/foreach}}
          </ul>
        </fieldset>
      </td>
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$integration mb_field=description}}
        {{mb_field object=$integration field=description}}
      {{/me_form_field}}
    </tr>
    <tr>
        {{me_form_field nb_cells=1 mb_object=$integration mb_field=icon_name}}
          {{mb_field object=$integration field=icon_name hidden=true}}
          <input id="form-{{$integration->_guid}}" type="text" name="keywords" class="autocomplete" value="{{$integration->icon_name}}">
        {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field id="icon-url-container" nb_cells=1 mb_object=$integration mb_field=icon_url}}
        <span>
          {{mb_field object=$integration field=icon_url size=50 placeholder="URL de l'image"}}
          <button class="change notext compact" type="button" onclick="ContextualIntegration.displayIcon(this.form.icon_url.value)"></button>
          <img src="{{if $integration->icon_url|strpos:"fa" !== 0}}{{$integration->icon_url}}{{/if}}" height="16"/>
        </span>
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$integration mb_field=display_mode}}
        {{mb_field object=$integration field=display_mode}}
      {{/me_form_field}}
    </tr>
    <tr>
      <td></td>
      <td>
        {{mb_include module=system template=inc_form_table_footer object=$integration}}
      </td>
    </tr>
  </table>
</form>

{{if $integration->_id}}
  <table class="main tbl">
    <tr>
      <th class="title" colspan="3">
        <button class="new me-float-right me-margin-right-0" type="button" onclick="ContextualIntegration.createLocation({{$integration->_id}})"
                style="float:left;margin-right:-130px;">
          {{tr}}CContextualIntegrationLocation-title-create{{/tr}}
        </button>
        {{tr}}CContextualIntegration-back-integration_locations{{/tr}}
      </th>
    </tr>
    <tr>
      <th>{{mb_title class=CContextualIntegrationLocation field=location}}</th>
      <th>{{mb_title class=CContextualIntegrationLocation field=button_type}}</th>
      <th class="narrow">{{tr}}CContextualIntegrationLocation.action{{/tr}}</th>
    </tr>

    {{foreach from=$integration->_ref_locations item=_location}}
      <tr>
        <td>{{mb_value object=$_location field=location}}</td>
        <td>{{mb_value object=$_location field=button_type}}</td>
        <td class="me-text-align-center">
          <button class="edit notext compact" onclick="ContextualIntegration.editLocation({{$_location->_id}})">
            {{tr}}Edit{{/tr}}
          </button>
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="3" class="empty">{{tr}}CContextualIntegrationLocation.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
{{/if}}
