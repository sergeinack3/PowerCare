{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  printStats = function () {
    var url = new Url("dPstats", "print_tab_occupation_salle");
    url.addParam("print", 1);
    url.popup(600, 550, $T('mod-dPstats-tab-print_tab_occupation_salle'));
  };

  {{if $print}}
  Main.add(function () {
    window.print();
    window.setTimeout(function () {
      this.close();
    }, 250);
  });
  {{/if}}
</script>

{{if !$print}}
  <button type="button" class="print" onclick="printStats();">{{tr}}Print{{/tr}}</button>
{{/if}}
<ul>
  <li>Debut : <strong>{{$debut|date_format:$conf.date}}</strong></li>
  <li>Fin : <strong>{{$fin|date_format:$conf.date}}</strong></li>
  <li>Actes prévus : <strong>{{$codeCCAM}}</strong></li>
  <li>Type d'admission : <strong>{{$type_hospi}}</strong></li>
  <li>Spécialité : <strong>{{$discipline}}</strong></li>
  <li>Bloc opératoire : <strong>{{$bloc}}</strong></li>
  <li>Salle : <strong>{{$salle}}</strong></li>
  <li>Hors plage : <strong>{{tr}}{{$hors_plage|ternary:"Yes":"No"}}{{/tr}}</strong></li>
</ul>

<table class="tbl">
  <tr>
    <th rowspan="2">Spécialité</th>
    <th rowspan="2">fonction</th>
    <th rowspan="2">Praticien</th>
    <th colspan="4">Durée totale (en heure)</th>
    <th colspan="4">Durée moyenne / interv. (en minutes)</th>
  </tr>
  <tr>
    <th>Intervention</th>
    <th>Référence</th>
    <th>Occupation</th>
    <th>Référence</th>
    <th>Intervention</th>
    <th>Référence</th>
    <th>Occupation</th>
    <th>Référence</th>
  </tr>
  {{foreach from=$tableau item=_praticien}}
    <tr>
      <td class="text">{{$_praticien.user->_ref_discipline}}</td>
      <td class="text">{{$_praticien.user->_ref_function}}</td>
      <td class="text">{{$_praticien.user}}</td>
      <td>{{$_praticien.duree_totale_intervs|string_format:"%.2f"}}</td>
      <td>{{$_praticien.nb_interv_intervs}}/{{$_praticien.total_interventions}} intervs.</td>
      <td>{{$_praticien.duree_totale_occupation|string_format:"%.2f"}}</td>
      <td>{{$_praticien.nb_interv_occupation}}/{{$_praticien.total_interventions}} intervs.</td>
      <td>{{$_praticien.duree_moyenne_intervs|string_format:"%.2f"}}</td>
      <td>{{$_praticien.nb_interv_intervs}}/{{$_praticien.total_interventions}} intervs.</td>
      <td>{{$_praticien.duree_moyenne_occupation|string_format:"%.2f"}}</td>
      <td>{{$_praticien.nb_interv_occupation}}/{{$_praticien.total_interventions}} intervs.</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="11">Aucun praticien</td>
    </tr>
  {{/foreach}}
  <tr>
    <th colspan="3">Total</th>
    <td>{{$duree_totale_intervs|string_format:"%.2f"}}</td>
    <td>{{$nb_interv_intervs}}/{{$total_interventions}} intervs.</td>
    <td>{{$duree_totale_occupation|string_format:"%.2f"}}</td>
    <td>{{$nb_interv_occupation}}/{{$total_interventions}} intervs.</td>
    <td>{{$duree_moyenne_intervs|string_format:"%.2f"}}</td>
    <td>{{$nb_interv_intervs}}/{{$total_interventions}} intervs.</td>
    <td>{{$duree_moyenne_occupation|string_format:"%.2f"}}</td>
    <td>{{$nb_interv_occupation}}/{{$total_interventions}} intervs.</td>
  </tr>
</table>
