{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=dhe_multiple ajax=true}}

{{assign var=max_rank value=1}}
{{if $multiple}}
  {{assign var=max_rank value=4}}
{{/if}}

{{assign var=hospi_comp_jour value="dPplanningOp CSejour hospi_comp_jour"|gconf}}

<script>
  function showProgramme(plage_id, rank) {
    new Url("planningOp", "ajax_prog_plageop")
      .addParam("plageop_id", plage_id)
      .addParam("chir_id", {{$chir}})
      .addParam("multiple", '{{$multiple}}')
      .addParam("rank", rank)
      .requestUpdate("prog_plageop_" + DHEMultiple.actual_rank);
  }

  function setClose(date, salle_id, plage_id) {
    var form = getForm("plageSelectorFrm1");
    window.parent.PlageOpSelector.set(form, plage_id, salle_id, date);
  }

  Main.add(function() {
    getForm("chgDate").onsubmit();

    {{if $new_dhe}}
      var oFormSejour = window.parent.getForm("sejourEdit");
    {{else}}
      var oFormSejour = window.parent.getForm("editSejour");
    {{/if}}

    {{foreach from=1|range:$max_rank item=rank}}
      var form = getForm("plageSelectorFrm{{$rank}}");
      $V(form.admission, "aucune");

      {{if $rank == 1}}
        if (!oFormSejour.sejour_id.value) {
      {{/if}}
          {{if $protocole->_id && $protocole->admission}}
            $V(form.admission, '{{$protocole->admission}}');
          {{else}}
            $V(form.admission, ["ambu", "exte" {{if $hospi_comp_jour}}, "comp"{{/if}}].include(oFormSejour.type.value) ? "jour" : "veille");
          {{/if}}
      {{if $rank == 1}}
        }
      {{/if}}
    {{/foreach}}
  });
</script>

<table class="main tbl">
  <tr>
    <th class="title" style="font-size: 1.5em;">
      {{if $multiple}}
        <button type="button" class="add notext" style="float: right;" onclick="DHEMultiple.addSlot();">Ajouter un séjour</button>
        <button type="button" class="erase notext" style="float: right;" onclick="DHEMultiple.resetSlots();">Vider les DHE complétées</button>
        <button type="button" class="tick" style="float: right;" onclick="DHEMultiple.validate();">{{tr}}Validate{{/tr}}</button>
      {{/if}}
      <form name="chgDate" method="get" onsubmit="return onSubmitFormAjax(this, null, 'list_plages');">
        <input type="hidden" name="m" value="planningOp" />
        <input type="hidden" name="a" value="ajax_list_plages" />
        <input type="hidden" name="dialog" value="1" />
        <input type="hidden" name="curr_op_time" value="{{$curr_op_time}}" />
        <input type="hidden" name="chir" value="{{$chir}}" />
        <input type="hidden" name="group_id" value="{{$group_id}}" />
        <input type="hidden" name="multiple" value="{{$multiple}}" />

        <button type="button" class="left notext" onclick="
          if (this.form.date_plagesel.selectedIndex) {
            $V(this.form.date_plagesel, this.form.date_plagesel.options[this.form.date_plagesel.selectedIndex - 1].value);
          }">&lt; &lt;</button>

        <select name="date_plagesel" onchange="this.form.onsubmit();">
        {{foreach from=$listMonthes key=curr_key_month item=curr_month}}
          <option value="{{$curr_month.date}}" {{if $curr_key_month == 0}}selected{{/if}}>
            {{$curr_month.month}}
          </option>  
        {{/foreach}}
        </select>
        
        <button type="button" class="right notext" onclick="
          if (this.form.date_plagesel.selectedIndex < (this.form.date_plagesel.options.length - 1)) {
            $V(this.form.date_plagesel, this.form.date_plagesel.options[this.form.date_plagesel.selectedIndex + 1].value);
          }">&lt; &lt;</button>
      </form>
    </th>
  </tr>
</table>


<table class="main layout">
  <tr class="line_multiple">
    <td {{if $multiple}}style="width: 28%;"{{else}}class="halfPane"{{/if}} id="list_plages"></td>

    {{foreach from=1|range:$max_rank item=rank}}
    <td {{if $multiple}}style="{{math equation=72/x x=$max_rank}}%"{{/if}}>
      {{mb_include module=planningOp template=inc_form_admission_patient}}
    </td>
    {{/foreach}}
  </tr>
</table>