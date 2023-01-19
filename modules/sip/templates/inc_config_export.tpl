{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=sip script=SIP}}

<form name="editConfigExport" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    {{assign var="mod" value="sip"}}
    <tr>
      <th class="title" colspan="10">{{tr}}config-{{$mod}}-export{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_str var=export_segment}}

    {{mb_include module=system template=inc_config_str var=export_id_min}}
    
    {{mb_include module=system template=inc_config_str var=export_id_max}}

    {{mb_include module=system template=inc_config_str var=export_date_min}}

    {{mb_include module=system template=inc_config_str var=export_date_max}}

    {{mb_include module=system template=inc_config_str var=batch_count}}

    {{mb_include module=system template=inc_config_bool var=pat_no_ipp}}
 
    {{assign var=var    value="export_dest"}}
    {{assign var=field  value="$m[$var]"}}
    {{assign var=value  value=$conf.$m.$var}}
    {{assign var=locale value=config-$m-$var}}
    <tr>
      <th>
        <label for="{{$field}}" title="{{tr}}{{$locale}}-desc{{/tr}}">
          {{tr}}{{$locale}}{{/tr}}
        </label>  
      </th>

      <td>
        <select class="str" name="{{$field}}">
          {{foreach from=$receivers item=_receiver}}
            <option value="{{$_receiver->_id}}" {{if $value == $_receiver->_id}}selected{{/if}}>
              {{$_receiver}}
           </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="10">
        <button class="modify me-primary">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<table class="tbl">
  <tr>
    <th class="title" colspan="100">{{tr}}sip-export-classes{{/tr}}</th>
  </tr>

  <tr>
    <td class="narrow">
      {{tr}}sip-export-class{{/tr}} '{{tr}}CPatient{{/tr}}'
    </td>
    <td class="narrow">
      <button type="button" class="new me-primary" onclick="SIP.doExport('start', 'patient')">
        {{tr}}Start{{/tr}}
      </button>
      <button type="button" class="change" onclick="SIP.doExport('retry', 'patient')">
        {{tr}}Retry{{/tr}}
      </button>
      <button type="button" class="tick" onclick="SIP.doExport('continue', 'patient')">
        {{tr}}Continue{{/tr}}
      </button>
    </td>
    <td id="export-patient"></td>
  </tr>
</table>