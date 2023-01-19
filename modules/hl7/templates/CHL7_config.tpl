{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigHL7" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}
  
  <table class="form">
    {{mb_include module=system template=inc_config_str var=tag_default}}
    
    {{assign var=hl7v2_versions value='Ox\Interop\Hl7\CHL7v2::getInternationalVersions'|static_call:null}}
    {{assign var=list_hl7v2_versions value='|'|implode:$hl7v2_versions}}
    {{mb_include module=system template=inc_config_enum var=default_version values=$list_hl7v2_versions}}

    {{assign var=hl7v2_versions value='Ox\Interop\Hl7\CHL7v2::getFRAVersions'|static_call:null}}
    {{assign var=list_hl7v2_versions value='|'|implode:$hl7v2_versions}}
    {{mb_include module=system template=inc_config_enum var=default_fr_version values=$list_hl7v2_versions}}

    <tr>
      <td colspan="2"> <hr /> </td>
    </tr>
    
    {{mb_include module=system template=inc_config_bool var=strictSejourMatch}}
    
    {{mb_include module=system template=inc_config_str var=indeterminateDoctor}}
    {{mb_include module=system template=inc_config_bool var=doctorActif}}
    
    {{mb_include module=system template=inc_config_str var=importFunctionName}}

    {{mb_include module=system template=inc_config_str var=type_antecedents_adt_a60}}
    {{mb_include module=system template=inc_config_str var=appareil_antecedents_adt_a60}}

    <tr>
      <td class="button" colspan="10">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>