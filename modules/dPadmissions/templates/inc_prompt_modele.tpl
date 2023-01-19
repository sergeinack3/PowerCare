{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm("chooseDoc");
    new Url("compteRendu", 'autocomplete')
      .addParam("user_id", User.id)
      .addParam("function_id", User["function"]["id"])
      .addParam("object_class", "CSejour")
      .addParam("object_id", "")
      .autoComplete(form.keywords_modele, null, {
        method: "get",
        minChars: 2,
        afterUpdateElement: function(input, selected) {
          var modele_id = selected.down(".id").getText();
          var modele_name = selected.down("div").getText();
          $V(form.modele_id, modele_id);
          $V(form.keywords_modele, modele_id != 0 ? modele_name : "");
        },
        dropdown: true,
        width: "250px"});
  });
</script>

<div style="display: none" id="area_prompt_modele">
  <form name="download_etiqs" method="post" action="?m=hospi&raw=ajax_print_etiquettes_sejours" target="_blank" class="prepared">
    <input type="hidden" name="sejours_ids" value="" />
    <input type="hidden" name="modele_etiquette_id" />
  </form>

  <form name="chooseDoc" method="post" action="?m=compteRendu&raw=ajax_generate_docs_sejour" target="_blank">
    <input type="hidden" name="sejours_ids" value="" />
    <table class="form me-no-box-shadow">
      <tr>
        <th>
          Choix du modèle :
        </th>
        <td>
          <input type="text" name="keywords_modele"  value="" class="autocomplete str" autocomplete="off" />
          <button type="button" class="print me-primary"
                  onclick="if (Admissions.printForSelection($V(this.form.modele_id))) { Admissions.afterPrint(); }">{{tr}}Print{{/tr}}</button>
          <input type="hidden" name="modele_id" value="" />
        </td>
      </tr>
      <tr>
        <td colspan="2" class="button">
          <button type="button" class="print" onclick="Admissions.printFichesAnesth();">Imprimer les fiches d'anesthésie</button>
          {{if "planSoins"|module_active && "planSoins general show_bouton_plan_soins"|gconf}}
            <button type="button" class="print" onclick="Admissions.printPlanSoins();">Imprimer les plans de soins</button>
          {{/if}}
          <button type="button" class="print" onclick="Admissions.chooseEtiquette();">{{tr}}CModeleEtiquette.print_labels{{/tr}}</button>
          <br />
          <button type="button" class="close me-margin-4 me-tertiary" onclick="Admissions.afterPrint();">{{tr}}Close{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>
