{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
changeType = function() {
  var form = getForm("Edit-CRetrocession");
  if (form.type.value == "autre") {
    $('see_pct_pm').show();
    $('see_pct_pt').show();
    $('see_montant').hide();
    form.montant.value = 0;
  }
  else {
    $('see_montant').show();
    $('see_pct_pm').hide();
    form.pct_pm.value = 0;
    $('see_pct_pt').hide();
    form.pct_pt.value = 0;
  }
};

Main.add(function () {
  var form = getForm("Edit-CRetrocession");

  {{if "dPccam codage use_cotation_ccam"|gconf}}
    form.code_class.options[3].hide();
    form.code_class.options[4].hide();
    form.code_class.options[3].disabled=true;
    form.code_class.options[4].disabled=true;
  {{/if}}
});
</script>

<form name="Edit-CRetrocession" action="?m={{$m}}" method="post" onsubmit="Retrocession.submit(this);">
  {{mb_key    object=$retrocession}}
  {{mb_class  object=$retrocession}}
  <input type="hidden" name="del" value="0"/>
  <table class="form">
  {{mb_include module=system template=inc_form_table_header object=$retrocession}}
    <tr>
      <th>{{mb_label object=$retrocession field=praticien_id}}</th>
      <td>{{mb_field object=$retrocession field=praticien_id options=$listPrat}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$retrocession field=nom}}</th>
      <td>{{mb_field object=$retrocession field=nom}}</td>
    </tr>
  <tr>
    <th>{{mb_label object=$retrocession field=type}}</th>
    <td>{{mb_field object=$retrocession field=type onchange="changeType();"}}</td>
  </tr>
  <tr id="see_montant" {{if $retrocession->type == "autre"}} style="display:none;"{{/if}}>
    <th>{{mb_label object=$retrocession field=valeur}}</th>
    <td>{{mb_field object=$retrocession field=valeur}}</td>
  </tr>
  <tr id="see_pct_pm" {{if $retrocession->type != "autre"}} style="display:none;"{{/if}}>
    <th>{{mb_label object=$retrocession field=pct_pm}}</th>
    <td>{{mb_field object=$retrocession field=pct_pm}}</td>
  </tr>
  <tr id="see_pct_pt" {{if $retrocession->type != "autre"}} style="display:none;"{{/if}}>
    <th>{{mb_label object=$retrocession field=pct_pt}}</th>
    <td>{{mb_field object=$retrocession field=pct_pt}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$retrocession field=code_class}}</th>
    <td>{{mb_field object=$retrocession field=code_class emptyLabel="Choose"}}</td>
  </tr>
  <tr>
    <th class="narrow">{{mb_label class=CFactureItem field=code}}</th>
  </tr>
  <tr>
    <th>{{mb_label object=$retrocession field=use_pm}}</th>
    <td>{{mb_field object=$retrocession field=use_pm}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$retrocession field=active}}</th>
    <td>{{mb_field object=$retrocession field=active}}</td>
  </tr>
  <tr>
    <td class="button" colspan="2">
      {{if $retrocession->_id}}
      <button class="submit" type="button" onclick="Retrocession.submit(this.form);">{{tr}}Save{{/tr}}</button>
      <button class="trash" type="button" onclick="Retrocession.confirmDeletion(this.form);">{{tr}}Delete{{/tr}}</button>
      {{else}}
      <button class="submit" type="button" onclick="Retrocession.submit(this.form);">{{tr}}Create{{/tr}}</button>
      {{/if}}
    </td>
  </tr>
  </table>
</form>
