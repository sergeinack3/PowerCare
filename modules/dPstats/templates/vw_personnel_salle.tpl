{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

  Main.add(function () {
    var form = getForm("personnelSalle");
    Calendar.regField(form.deb_personnel);
    Calendar.regField(form.fin_personnel);
  });

</script>

<form name="personnelSalle" action="?" method="get" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="dPstats" />
  <table class="main form">
    <tr>
      <th colspan="6" class="category">
        Bilan pour le personnel :
        {{tr}}CPersonnel.emplacement.op{{/tr}}
      </th>
    </tr>
    <tr>
      <th>Praticien</th>
      <td>
        <select name="prat_personnel">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser selected=$prat_personnel list=$listPrats}}
        </select>
      </td>
      <th><label for="deb_personnel" title="Date de début">Début</label></th>
      <td><input type="hidden" name="deb_personnel" class="notNull date" value="{{$deb_personnel}}" /></td>
      <th><label for="fin_personnel" title="Date de fin">Fin</label></th>
      <td><input type="hidden" name="fin_personnel" class="notNull date" value="{{$fin_personnel}}" /></td>
    </tr>
    <tr>
      <td class="button" colspan="6">
        <button class="search" type="submit">Afficher</button>
      </td>
    </tr>
  </table>

</form>
{{if $prat_personnel}}
  <table class="main tbl">
    <tr>
      <th rowspan="2">Date</th>
      <th rowspan="2">Salle</th>
      <th rowspan="2">Nb interv.</th>
      <th colspan="3">Durées</th>
      <th colspan="2">Aides op.</th>
      <th colspan="2">Panseuses</th>
      <th colspan="2">IADE</th>
    </tr>
    <tr>
      <th>prévue</th>
      <th>première à la dernière</th>
      <th>totale interv. (interv. pris en compte)</th>
      <th>nb prévu</th>
      <th>durée notée</th>
      <th>nb prévu</th>
      <th>durée notée</th>
      <th>nb prévu</th>
      <th>durée notée</th>
    </tr>
    {{foreach from=$listPlages item=curr_plage}}
      <tr>
        <td>{{$curr_plage->date|date_format:$conf.date}}</td>
        <td>{{$curr_plage->_ref_salle->_view}}</td>
        <td>{{$curr_plage->_ref_operations|@count}}</td>
        <td>{{$curr_plage->_duree_prevue|date_format:$conf.time}}</td>
        <td>{{$curr_plage->_duree_first_to_last|date_format:$conf.time}}</td>
        <td>
          {{$curr_plage->_duree_total_op|date_format:$conf.time}}
          ({{$curr_plage->_op_for_duree_totale}}/{{$curr_plage->_ref_operations|@count}})
        </td>
        <td>{{$curr_plage->_ref_affectations_personnel.op|@count}}</td>
        <td>{{$curr_plage->_duree_total_personnel.op.days_duree}}
          j {{$curr_plage->_duree_total_personnel.op.duree|date_format:$conf.time}}</td>
        <td>{{$curr_plage->_ref_affectations_personnel.op_panseuse|@count}}</td>
        <td>{{$curr_plage->_duree_total_personnel.op_panseuse.days_duree}}
          j {{$curr_plage->_duree_total_personnel.op_panseuse.duree|date_format:$conf.time}}</td>
        <td>{{$curr_plage->_ref_affectations_personnel.iade|@count}}</td>
        <td>{{$curr_plage->_duree_total_personnel.iade.days_duree}}
          j {{$curr_plage->_duree_total_personnel.iade.duree|date_format:$conf.time}}</td>
      </tr>
    {{/foreach}}
    <tr>
      <td colspan="3"></td>
      <td>
        <strong>
          {{$total.days_duree_prevue}}j
          {{$total.duree_prevue|date_format:$conf.time}}
        </strong>
      </td>
      <td>
        <strong>
          {{$total.days_duree_first_to_last}}j
          {{$total.duree_first_to_last|date_format:$conf.time}}
        </strong>
      </td>
      <td>
        <strong>
          {{$total.days_duree_reelle}}j
          {{$total.duree_reelle|date_format:$conf.time}}
        </strong>
      </td>
      <td></td>
      <td>
        <strong>
          {{$total.personnel.op.days_duree}}j
          {{$total.personnel.op.duree|date_format:$conf.time}}
        </strong>
      </td>
      <td></td>
      <td>
        <strong>
          {{$total.personnel.op_panseuse.days_duree}}j
          {{$total.personnel.op_panseuse.duree|date_format:$conf.time}}
        </strong>
      </td>
      <td></td>
      <td>
        <strong>
          {{$total.personnel.iade.days_duree}}j
          {{$total.personnel.iade.duree|date_format:$conf.time}}
        </strong>
      </td>
    </tr>
  </table>
{{/if}}