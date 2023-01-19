{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="stats" script="display_graph"}}

<script>
  DisplayGraph.addFiltersParam = function (url) {
    var oForm = DisplayGraph.filterForm;
    url.addElement(oForm._date_min);
    url.addElement(oForm._date_max);
    url.addElement(oForm._complete_months);
    url.addElement(oForm.service_id);
    url.addElement(oForm.type);
    url.addElement(oForm.prat_id);
    url.addElement(oForm.discipline_id);
    url.addElement(oForm.septique);
    url.addElement(oForm.type_data);
  };

  DisplayGraph.qualiteHospi = function () {
    var url = new Url("stats", "vw_hospi_qualite_donnees");
    DisplayGraph.getFilterForm();
    DisplayGraph.addFiltersParam(url);
    url.requestModal();
  };

  DisplayGraph.statsPatientsParTypeHospiParService = function () {
    var form = getForm("stats_params");
    var url = new Url("stats", "ajax_patients_by_type_by_service");
    url.addFormData(form);
    url.requestModal();
  }
</script>

<form name="stats_params" action="?" method="get" onsubmit="return false;">

  <table class="main form">
    <tr>
      <th>{{mb_label object=$filter field="_date_min"}}</th>
      <td>{{mb_field object=$filter field="_date_min" form="stats_params" canNull="false" register=true}}</td>

      <th>{{mb_label object=$filter field="_service"}}</th>
      <td>
        <select name="service_id">
          <option value="0">&mdash; Tous les services</option>
          {{foreach from=$listServices item=curr_service}}
            <option value="{{$curr_service->service_id}}" {{if $curr_service->service_id == $filter->_service}}selected{{/if}}>
              {{$curr_service->nom}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field="_date_max"}}</th>
      <td>{{mb_field object=$filter field="_date_max" form="stats_params" canNull="false" register=true}} </td>

      <th>{{mb_label object=$filter field="praticien_id"}}</th>
      <td>
        <select name="prat_id">
          <option value="0">&mdash; Tous les praticiens</option>
          {{foreach from=$listPrats item=curr_prat}}
            <option value="{{$curr_prat->user_id}}" {{if $curr_prat->user_id == $filter->praticien_id}}selected{{/if}}>
              {{$curr_prat->_view}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th><label for="_complete_months" title="Mois complets">Mois complets</label></th>
      <td>
        <input type="checkbox" name="_complete_months" />
      </td>

      <th>{{mb_label object=$filter field="_specialite"}}</th>
      <td>
        <select name="discipline_id">
          <option value="0">&mdash; Toutes les spécialités</option>
          {{foreach from=$listDisciplines item=curr_disc}}
            <option value="{{$curr_disc->discipline_id}}" {{if $curr_disc->discipline_id == $filter->_specialite}}selected{{/if}}>
              {{$curr_disc->_view}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field="type"}}</th>
      <td>
        <select name="type">
          <option value="">&mdash; Tous les types d'hospi</option>
          <option value="1" {{if $filter->type == "1"}}selected="selected"{{/if}}>Hospi complètes + ambu</option>
          {{foreach from=$filter->_specs.type->_locales key=key_hospi item=curr_hospi}}
            <option value="{{$key_hospi}}" {{if $key_hospi == $filter->type}}selected{{/if}}>
              {{$curr_hospi}}
            </option>
          {{/foreach}}
        </select>
      </td>
      <th>Uniquement {{mb_label object=$filter field="septique" typeEnum="checkbox"}}</th>
      <td>{{mb_field object=$filter field="septique" typeEnum="checkbox"}}</td>
    </tr>

    <tr>
      <th><label for="type_data" title="Type de données prises en compte">Type de données</th>
      <td>
        <select name="type_data">
          <option value="prevue" {{if $type_data == "prevue"}}selected{{/if}}>Prévues</option>
          <option value="reelle" {{if $type_data == "reelle"}}selected{{/if}}>Réelles</option>
        </select>
      </td>
      <td colspan="2"></td>
    </tr>
  </table>

</form>

<table class="main">
  <tr>
    <th colspan="2">
      <hr />
      Sejours
    </th>
  </tr>
  <tr>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Répartition par service du nombre de patients
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('patparservice')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Répartition du nombre d'admissions par type d'hospitalisation
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('patpartypehospi')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
  </tr>
  <tr>
    <td class="button">
      <div class="small-info" style="text-align: center">
        Répartition du nombre de nuits par service
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('jourparservice')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
    <td>
      <div class="small-info" style="text-align: center">
        Qualité des données
        <br />
        <button type="button" class="list"
                onclick="DisplayGraph.qualiteHospi()">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
  </tr>
  <tr>
    <td>
      <div class="small-info" style="text-align: center">
        Répartition du nombre de patients par type d'hospitalisation par service
        <br />
        <button type="button" class="list"
                onclick="DisplayGraph.statsPatientsParTypeHospiParService()">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
    <td>
      <div class="small-info" style="text-align: center">
        Evolution du nombre de jours-patients
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('jourspatients');">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
  </tr>
</table>