{{*
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=context script=ContextualIntegration ajax=true}}

{{mb_default var=return_line value=false}}
{{mb_default var=style_used value=""}}
{{mb_default var=show_menu value=false}}

{{foreach from=$locations item=_location}}
  {{assign var=_integration value=$_location->_ref_integration}}
  {{assign var=_data_title value=$_integration->title}}
  {{assign var=_display_mode value=$_integration->display_mode}}
  {{assign var=_icon_url value=$_integration->icon_url}}
  {{assign var=_fa value=false}}
  {{if $_integration->icon_url|strpos:"fa" === 0}}
      {{assign var=_fa value=true}}
  {{/if}}

  <style>
    #integration-{{$uid}} button.integration-{{$_integration->_id}}::before {
      content: "";
      background-image: url({{$_icon_url}});
      height: 18px;
      {{if $_location->button_type == "button_text"}}
        width: 18px;
      {{else}}
        width: 100%;
      {{/if}}
      background-size: contain;
      background-position: center center;
      background-repeat: no-repeat;
    }
    #integration-{{$uid}} button.dd-integration::before {
      vertical-align: middle;
    }
  </style>

  {{if !$show_menu}}
    <button type="button"
            class="not-printable me-primary"
            style="margin: 0 5px 0 0; border: 0; background-color: white!important;"
            data-url="{{$_integration->_url}}"
            data-title="{{$_data_title}}"
            data-display_mode="{{$_display_mode}}"
            onclick="ContextualIntegration.do_integration(this);">
      {{if $_fa}}
        <i class="{{$_icon_url}}" style="font-size: 1em;"></i>
      {{else}}
        <img src="{{$_icon_url}}" height="16" />
      {{/if}}

      {{if $_location->button_type == "button" || $_location->button_type == "button_text"}}
        <span>{{$_integration->title}}</span>
      {{/if}}
    </button>
  {{else}}
    {{assign var=button_attr value="data-url=`$_integration->_url` data-title=$_data_title data-display_mode=$_display_mode"}}
    {{if $_location->button_type == "button" || $_location->button_type == "button_text"}}
      {{assign var=title value="$_data_title"}}
    {{else}}
      {{assign var=title value=""}}
    {{/if}}
    {{if $_location->button_type == "icon" || $_location->button_type == "button_text"}}
      {{if $_fa && $_integration->icon_url|strpos:"fa" === 0}}
        {{assign var=icon_value value="ic-integration `$_icon_url`"}}
      {{else}}
        {{assign var=icon_value value="dd-integration integration-`$_integration->_id`"}}
      {{/if}}
    {{else}}
      {{assign var=icon_value value=""}}
    {{/if}}

    {{me_button label=$title icon=$icon_value title=$_integration->description
      onclick="ContextualIntegration.do_integration(this);" attr=$button_attr}}
  {{/if}}
{{/foreach}}

{{if $show_menu}}
    {{me_dropdown_button button_icon="opt" button_id="dropdown-context" button_class="me-primary not-printable notext"
    button_label="CContextualIntegration.show_menu_title"
    container_class="me-dropdown-button-right"}}
{{/if}}
