{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPplanningOp" script="operation"}}

<script>
  function printFicheAnesth(dossier_anesth_id) {
    new Url("cabinet", "print_fiche").
    addParam("dossier_anesth_id", dossier_anesth_id).
    popup(700, 500, "printFiche");
  }

  function editVisite(operation_id) {
    new Url("planningOp", "edit_visite_anesth").
    addParam("operation_id", operation_id).
    popup(800, 500, "editVisite");
  }

  function reloadLineVisiteAnesth(operation_guid) {
    var url = new Url("planningOp", "ajax_line_visite_anesth");
    url.addParam("operation_guid", operation_guid);
    url.requestUpdate("visite_anesth_"+operation_guid);
  }

  function refreshPlanningIntervAnesth() {
    var url = new Url("planningOp", "vw_idx_planning");
    url.addParam("only_list_anesth", 1);
    url.addFormData(getForm('selectOptions'));
    var selectPraticien = getForm("selectPraticien");
    url.addParam("date", $V(selectPraticien.date));
    url.addParam("selPrat", $V(selectPraticien.selPrat));
    url.requestUpdate("planning_intervs_anesth");
  }

  Main.add(function() {
    Calendar.regField(getForm("selectPraticien").date, null, {noView: true});
    if ($('type_sejour')) {
      Control.Tabs.create('type_sejour', true);
    }
  });
</script>

<table class="main">
  <tr>
    <th>
      <form name="selectPraticien" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <label for="selPrat">Praticien</label>
        <select name="selPrat" onchange="this.form.submit()" style="max-width: 150px;">
          <option value="-1">&mdash; Choisir un praticien</option>
          <option value="all" {{if $selPrat == 'all'}}selected{{/if}}>&mdash; Tous les praticiens</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$listPrat selected=$selPrat}}
        </select>
        - Interventions du {{$date|date_format:$conf.longdate}}
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </form>
      <form name="selectOptions" method="get">
        <input type="hidden" name="sans_anesth" value="{{$sans_anesth}}"/>
        <input type="hidden" name="canceled" value="{{$canceled}}"/>
        <input type="hidden" name="sejour_ambu" value="{{$sejour_ambu}}"/>
        <input type="hidden" name="sejour_comp" value="{{$sejour_comp}}"/>
        <input type="hidden" name="hors_plage" value="{{$hors_plage}}"/>
        <label>
          <input type="checkbox" name="_canceled" {{if $canceled}}checked{{/if}}
            onclick="$V(this.form.canceled, this.checked ? 1 : 0); refreshPlanningIntervAnesth();"/>
            Afficher les annulées
        </label>
        <label>
          <input type="checkbox" name="_sans_anesth" {{if $sans_anesth}}checked{{/if}}
                 onclick="$V(this.form.sans_anesth, this.checked ? 1 : 0); refreshPlanningIntervAnesth();"/>
          Inclure les interventions sans anesthésiste
        </label>
        <br/>
        <label>
          <input type="checkbox" name="_sejour_ambu" {{if $sejour_ambu}}checked{{/if}}
            onclick="$V(this.form.sejour_ambu, this.checked ? 1 : 0); refreshPlanningIntervAnesth();"/>
          {{tr}}CSejour.type.ambu{{/tr}}
        </label>
        <label>
          <input type="checkbox" name="_sejour_comp" {{if $sejour_comp}}checked{{/if}}
                 onclick="$V(this.form.sejour_comp, this.checked ? 1 : 0); refreshPlanningIntervAnesth();"/>
          {{tr}}CSejour.type.comp{{/tr}}
        </label>
        <label>
          <input type="checkbox" name="_hors_plage" {{if $hors_plage}}checked{{/if}}
            onclick="$V(this.form.hors_plage, this.checked ? 1 : 0); refreshPlanningIntervAnesth();"/>
          {{tr}}CSejour.type.hors_plage{{/tr}}
        </label>
      </form>
    </th>
  </tr>
  <tr>
    <td id="planning_intervs_anesth">
      {{mb_include module=planningOp template=vw_list_visite_anesth}}
    </td>
  </tr>
</table>