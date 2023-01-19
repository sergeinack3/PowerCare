{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editDiag" method="post" action="?" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$rpu}}
  {{mb_key object=$rpu}}
  <input type="hidden" name="del" value="0" />
  <table class="form">
    <tr>
      <th class="category" colspan="2">{{tr}}CRPU{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_label object=$rpu field=diag_infirmier}}</th>
      <td>
        {{mb_field object=$rpu field=diag_infirmier onchange="this.form.onsubmit();" class="autocomplete" form="editDiag"
             aidesaisie="validate: function() { form.onsubmit();},
                         resetSearchField: 0,
                         resetDependFields: 0,
                         validateOnBlur: 0,
                         height: '100px'"}}
      </td>
    </tr>
  </table>
</form>