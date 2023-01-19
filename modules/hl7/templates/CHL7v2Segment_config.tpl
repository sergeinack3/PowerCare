{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig-CHL7v2Segment" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}
  <table class="form">
    {{assign var=class value=CHL7v2Segment}}    
    {{mb_include module=system template=inc_config_bool var=ignore_unexpected_z_segment}}
    
    <tr>
      <th class="title" colspan="2">{{tr}}{{$class}}PV1{{/tr}}</th>
    </tr>
    
    {{mb_include module=system template=inc_config_str var=PV1_3_2}}
    {{mb_include module=system template=inc_config_str var=PV1_3_3}}
    
    <tr>
      <td class="button" colspan="10">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>