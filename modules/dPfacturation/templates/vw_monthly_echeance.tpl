{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  Main.add(
    function() {
      getForm('monthly_echeance').nb_month.addSpinner({step : 1,min : 1});
      getForm('monthly_echeance').interest.addSpinner({step : 1,min : 1});
      Echeance.Monthly.updateFactureMontant(getForm('monthly_echeance'))
        .updateFactureMonths(getForm('monthly_echeance'));
    }
  );
</script>
<form name="monthly_echeance" action="" method="post" onsubmit="return Echeance.submit(this);">
  <input type="hidden" name="object_id"    value="{{$echeance->object_id}}"/>
  <input type="hidden" name="object_class" value="{{$echeance->object_class}}"/>
  <input type="hidden" name="montant_total" value="{{$montant_facture}}"/>
  <input type="hidden" name="echeance_interest" value=""/>
  <input type="hidden" name="montant_total_interest" value=""/>
  <table class="form">
    <tr>
      <th class="title me-th-new" colspan="2">
        {{tr}}CEcheance-title-create-mensualite{{/tr}}
      </th>
    </tr>
    <tr>
      <th>{{tr}}CEcheance.monthly first date{{/tr}}</th>
      <td>
        {{mb_field object=$echeance field=date form="monthly_echeance" canNull="false" register=true}}
      </td>
    </tr>
    <tr>
      <th>{{tr}}CEcheance.monthly number of months{{/tr}}</th>
      <td>
        <input name="nb_month" type="text" class="notNull number" size="2"
               value="{{'dPfacturation CReglement echeancier_default_nb_month'|gconf}}"
               onchange="Echeance.Monthly.updateFactureMonths(getForm('monthly_echeance'))"/>
      </td>
    </tr>
    <tr>
      <th>{{tr}}CEcheance.monthly interest{{/tr}}</th>
      <td>
        <input name="interest" type="text" class="notNull number" size="2"
               value="{{'dPfacturation CReglement echeancier_default_interest'|gconf}}"
               onchange="Echeance.Monthly.updateFactureMontant(getForm('monthly_echeance'))
                          .updateFactureMonths(getForm('monthly_echeance'))"/> %
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$facture field=montant_total}}</th>
      <td>
        {{$montant_facture}}{{$conf.currency_symbol|html_entity_decode}} * <strong id="echeance_interest"></strong> =
        <strong id="facture_montant_total_interest"></strong><strong>{{$conf.currency_symbol|html_entity_decode}}</strong>
      </td>
    </tr>
    <tr>
      <th></th>
      <td>
        <strong id="montant_monthly"></strong><strong>{{$conf.currency_symbol|html_entity_decode}}</strong>
        {{tr}}CEcheance.monthly each month{{/tr}}
        (<strong id="montant_last_month"></strong> {{tr}}CEcheance.monthly last month{{/tr}})
      </td>
    </tr>
    <tr>
      <th></th>
      <td>
        {{tr}}CEcheance.monthly during{{/tr}} <strong id="nb_month"></strong> {{tr}}CEcheance.monthly months{{/tr}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$echeance field=description}}</th>
      <td>
        {{mb_field object=$echeance field=description}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2"
          id="monthly_echeance_result">
        <button class="cancel" type="button" onclick="Control.Modal.close();">
          {{tr}}Cancel{{/tr}}
        </button>
        <button class="tick" type="button"
                onclick="Echeance.Monthly.generate(this.form)">
          {{tr}}Validate{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>