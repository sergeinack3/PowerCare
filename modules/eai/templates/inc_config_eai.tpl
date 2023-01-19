{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigEAI" action="?" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}
  <table class="form">
    {{assign var="mod" value="eai"}}
    <tr>
      <th class="title" colspan="10">{{tr}}config-{{$mod}}{{/tr}}</th>
    </tr>

    <tr>
      <th class="category" colspan="10">{{tr}}config-{{$mod}}-general{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_bool var=convert_encoding}}
    {{mb_include module=system template=inc_config_bool var=use_domain}}
    {{mb_include module=system template=inc_config_bool var=use_routers}}
    {{mb_include module=system template=inc_config_bool var=send_messages_with_same_group}}

    <tr>
      <th class="category" colspan="10">{{tr}}config-{{$mod}}-file-sources{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_num var=max_files_to_process numeric=true}}
    {{mb_include module=system template=inc_config_num var=nb_files_retention_mb_excludes numeric=true}}

    <tr>
      <th class="category" colspan="10">{{tr}}config-{{$mod}}-exchanges{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_num var=nb_max_export_csv numeric=true}}
    {{mb_include module=system template=inc_config_num var=exchange_format_delayed numeric=true}}
    {{mb_include module=system template=inc_config_num var=max_reprocess_retries numeric=true}}

    <tr>
      <th class="category" colspan="2">{{tr}}Purge{{/tr}}</th>
    </tr>
    {{assign var="class" value="CExchangeDataFormat"}}
    <tr>
      <th class="section" colspan="2">{{tr}}{{$class}}{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_num var=purge_probability numeric=true}}
    {{mb_include module=system template=inc_config_num var=purge_empty_threshold numeric=true}}
    {{mb_include module=system template=inc_config_num var=purge_delete_threshold numeric=true}}

    {{assign var="class" value="CExchangeTransportLayer"}}
    <tr>
      <th class="section" colspan="2">{{tr}}{{$class}}{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_num var=purge_probability numeric=true}}
    {{mb_include module=system template=inc_config_num var=purge_empty_threshold numeric=true}}
    {{mb_include module=system template=inc_config_num var=purge_delete_threshold numeric=true}}

    <tr>
      <td class="button" colspan="10">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<hr class="me-no-display"/>

<table class="tbl me-table-col-separated">
  <tr>
    <th class="title" colspan="10">{{tr}}{{$mod}}-resume{{/tr}}</th>
  </tr>
  <tr>
    <th class="me-text-align-center">{{tr}}CGroups{{/tr}}</th>
    <th class="me-text-align-center" colspan="2">Numéroteur</th>
    <th class="me-text-align-center" colspan="2">Serveur</th>
    <th class="me-text-align-center" colspan="2">Notifieur</th>
  </tr>
  <tr>  
    <th></th>
    <th class="me-text-align-center">IPP</th>
    <th class="me-text-align-center">NDA</th>
    <th class="me-text-align-center">SIP</th>
    <th class="me-text-align-center">SMP</th>
    <th class="me-text-align-center">SIP</th>
    <th class="me-text-align-center">SMP</th>
  </tr>
  {{foreach from=$groups item=_group}} 
    {{assign var=config value=$_group->_configs}}
    <tr>
      <td>{{$_group}}</td>
      <td class="{{if $_group->_is_ipp_supplier}}ok{{else}}error{{/if}}">
        {{tr}}bool.{{$_group->_is_ipp_supplier}}{{/tr}}</td>
      <td class="{{if $_group->_is_nda_supplier}}ok{{else}}error{{/if}}">
        {{tr}}bool.{{$_group->_is_nda_supplier}}{{/tr}}</td>
      <td></td>
      <td></td>
      <td class="{{if $config.sip_notify_all_actors}}ok{{else}}error{{/if}}">
        {{tr}}bool.{{$config.sip_notify_all_actors}}{{/tr}}</td>
      <td class="{{if $config.smp_notify_all_actors}}ok{{else}}error{{/if}}">
        {{tr}}bool.{{$config.smp_notify_all_actors}}{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>