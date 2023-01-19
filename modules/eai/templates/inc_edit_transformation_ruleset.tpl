{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit{{$transf_ruleset->_guid}}" action="?m={{$m}}" method="post"
      onsubmit="return onSubmitFormAjax(this, {
        onComplete: function (){
          document.location.reload()}
      });">
  {{mb_key object=$transf_ruleset}}
  {{mb_class object=$transf_ruleset}}
  <input type="hidden" name="del" value="0" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$transf_ruleset}}
    <tr>
      <th>{{mb_label object=$transf_ruleset field="name"}}</th>
      <td>{{mb_field object=$transf_ruleset field="name"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$transf_ruleset field="description"}}</th>
      <td>{{mb_field object=$transf_ruleset field="description"}}</td>
    </tr>

    <tr>
      {{mb_include module=system template=inc_form_table_footer object=$transf_ruleset
        options="{typeName: '', objName: '`$transf_ruleset`'}"
        options_ajax="function(){ document.location.reload();}"}}
    </tr>
  </table>
</form>
