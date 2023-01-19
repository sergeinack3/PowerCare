{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form_main = getForm('editPrecision');
    var form_second = getForm('precisionValeur');

    GestePerop.gestePeropAutocomplete(form_main);
    GestePerop.precisionValueAutocomplete(form_main, form_second, '{{$precision->_guid}}');
  });
</script>

<form name="editPrecision" method="post" action="" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="dPsalleOp" />
  {{mb_class object=$precision}}
  {{mb_key   object=$precision}}

  {{mb_field object=$precision field=group_id hidden=true}}
  {{mb_field object=$precision field=geste_perop_id hidden=true}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$precision}}

      <tr>
        <th>{{mb_label object=$precision field=libelle}}</th>
        <td>{{mb_field object=$precision field=libelle}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$precision field=description}}</th>
        <td>{{mb_field object=$precision field=description}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$precision field=actif}}</th>
        <td>{{mb_field object=$precision field=actif}}</td>
      </tr>

    {{mb_include module=system template=inc_form_table_footer object=$precision options_ajax="Control.Modal.close"}}
  </table>
</form>

<div id="precision_valeurs" style="margin-top: 15px;">
  {{if $precision->_id}}
    {{mb_include module=salleOp template=inc_vw_list_precision_valeurs precision_valeurs=$valeurs}}
  {{/if}}
</div>
