{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-precision_valeur" method="post" action="" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="dPsalleOp" />
  {{mb_class object=$precision_valeur}}
  {{mb_key   object=$precision_valeur}}

  {{mb_field object=$precision_valeur field=group_id hidden=true}}
  {{mb_field object=$precision_valeur field=geste_perop_precision_id hidden=true}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$precision_valeur}}
      <tr>
        <th>{{mb_label object=$precision_valeur field=valeur}}</th>
        <td>{{mb_field object=$precision_valeur field=valeur}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$precision_valeur field=actif}}</th>
        <td>{{mb_field object=$precision_valeur field=actif}}</td>
      </tr>

    {{mb_include module=system template=inc_form_table_footer object=$precision_valeur options_ajax="Control.Modal.close"}}
  </table>
</form>
