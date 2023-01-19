{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=eai script=interop_actor}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-interop-norm-domains', true);
  });

  checkAll = function(family_name, category_uid) {
    $$(".form-message-supported-"+family_name+"-"+category_uid).each(function(form) {
      form.onsubmit();
    });
  }
</script>

<table class="form">
  <tr>
    <td style="vertical-align: top; width: 250px" >
      <ul id="tabs-interop-norm-domains" class="control_tabs_vertical small">
        {{foreach from=$all_messages key=_domain item=_families}}
          <li>
            <a href="#{{$_domain}}" class="me-flex-wrap">
              {{tr}}{{$_domain}}{{/tr}}
              <br />
              <span class="compact">{{tr}}{{$_domain}}-desc{{/tr}}</span>
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>
    <td style="vertical-align: top;">
      {{foreach from=$all_messages key=_domain_name item=_domains}}
        <div id="{{$_domain_name}}" style="display: none;">
          <script type="text/javascript">
            Control.Tabs.create('tabs-'+'{{$_domain_name}}'+'-families', true);
          </script>

          <ul id="tabs-{{$_domain_name}}-families" class="control_tabs small">
            {{foreach from=$_domains item=_families}}
              {{assign var=_family_name value=$_families|getShortName}}

              <li class="me-tabs-flex">
                <a href="#{{$_family_name}}" class="me-flex-column">
                  {{tr}}{{$_family_name}}{{/tr}}
                  <br />
                  <span class="compact">{{tr}}{{$_family_name}}-desc{{/tr}}</span>
                </a>
              </li>
            {{/foreach}}
          </ul>

          <hr />

          {{assign var=data_format_module value=$data_format->_ref_module->mod_name}}

          {{foreach from=$_domains item=_families}}
            {{assign var=_family_name value=$_families|getShortName}}

            <div id="{{$_family_name}}" style="display: none;">
              <table class="tbl form">
                {{foreach from=$_families->_categories key=_category_name item=_messages_supported}}
                    {{unique_id var=category_uid numeric=true}}
                    {{assign var=module value=$data_format_module}}
                    {{if 'inc_message_supported_section_header'|tpl_exist:$data_format_module === false}}
                        {{assign var=module value="eai"}}
                    {{/if}}

                    {{mb_include module=$module template=inc_message_supported_section_header}}
                    {{foreach from=$_messages_supported item=_message_supported}}
                        {{unique_id var=message_uid numeric=true}}
                      <tr class="actor_message_supported_{{$category_uid}}" id="{{$message_uid}}">
                          {{mb_include template=inc_container_active_message_supported_form}}
                      </tr>
                    {{/foreach}}
                {{/foreach}}
              </table>
            </div>
          {{/foreach}}
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>
