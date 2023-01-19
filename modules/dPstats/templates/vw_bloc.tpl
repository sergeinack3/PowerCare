{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="planningOp" script="ccam_selector"}}
{{mb_script module="stats" script="display_graph"}}

<script>

  DisplayGraph.addFiltersParam = function (url) {
    var oForm = DisplayGraph.filterForm;
    url.addParam("_date_min", $V(oForm._date_min));
    url.addParam("_date_max", $V(oForm._date_max));
    url.addParam('_complete_months', $V(oForm._complete_months));
    url.addParam("codes_ccam", $V(oForm.codes_ccam));
    url.addParam("type", $V(oForm.type));
    url.addParam("prat_id", $V(oForm.prat_id));
    url.addParam("func_id", $V(oForm.func_id));
    url.addParam("discipline_id", $V(oForm.discipline_id));
    url.addParam("bloc_id", $V(oForm.bloc_id));
    url.addParam("salle_id", $V(oForm.salle_id));
    url.addParam("hors_plage", $V(oForm.hors_plage));
  };

  DisplayGraph.occupationSalleParPrat = function () {
    var oForm = getForm("stats_params");
    var url = new Url("dPstats", "print_tab_occupation_salle");
    url.addParam("date_debut", $V(oForm._date_min));
    url.addParam("date_fin", $V(oForm._date_max));
    url.addParam("CCAM", $V(oForm.codes_ccam));
    url.addParam("type", $V(oForm.type));
    url.addParam("prat_id", $V(oForm.prat_id));
    url.addParam("func_id", $V(oForm.func_id));
    url.addParam("discipline_id", $V(oForm.discipline_id));
    url.addParam("bloc_id", $V(oForm.bloc_id));
    url.addParam("salle_id", $V(oForm.salle_id));
    url.addParam("hors_plage", $V(oForm.hors_plage));
    url.requestModal();
  };

  DisplayGraph.codageQuality = function () {
    var oForm = getForm("stats_params");
    var url = new Url("stats", "codage_quality");
    url.addParam("date_debut", $V(oForm._date_min));
    url.addParam("date_fin", $V(oForm._date_max));
    url.addParam("CCAM", $V(oForm.codes_ccam));
    url.addParam("type", $V(oForm.type));
    url.addParam("prat_id", $V(oForm.prat_id));
    url.addParam("func_id", $V(oForm.func_id));
    url.addParam("discipline_id", $V(oForm.discipline_id));
    url.addParam("bloc_id", $V(oForm.bloc_id));
    url.addParam("salle_id", $V(oForm.salle_id));
    url.addParam("hors_plage", $V(oForm.hors_plage));
    url.requestModal();
  };

  DisplayGraph.listInterventions = function () {
    var oForm = getForm("stats_params");
    var url = new Url("dPstats", "download_csv_interventions", "raw");
    url.addParam("_date_min", $V(oForm._date_min));
    url.addParam("_date_max", $V(oForm._date_max));
    url.addParam("codes_ccam", $V(oForm.codes_ccam));
    url.addParam("type", $V(oForm.type));
    url.addParam("prat_id", $V(oForm.prat_id));
    url.addParam("func_id", $V(oForm.func_id));
    url.addParam("service_id", $V(oForm.service_id));
    url.addParam("discipline_id", $V(oForm.discipline_id));
    url.addParam("bloc_id", $V(oForm.bloc_id));
    url.addParam("salle_id", $V(oForm.salle_id));
    url.addParam("hors_plage", $V(oForm.hors_plage));
    url.popup(200, 100, "Extraction CSV");
  };

  DisplayGraph.displayANAP = function () {
    var form = getForm('stats_params');

    var url = new Url('stats', 'ajax_view_anap');
    url.addParam('prat_ids', $V(form.elements['prat_id']));
    url.addParam('bloc_ids', $V(form.elements['bloc_id']));
    url.addParam('salle_ids', $V(form.elements['salle_id']));
    url.addParam('discipline_ids', $V(form.elements['discipline_id']));
    url.requestModal('90%', '90%');
  };
</script>

<form name="stats_params" action="?" method="get" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="stats" />
  <input type="hidden" name="_chir" value="{{$app->user_id}}" />
  <input type="hidden" name="_class" value="" />
  <table class="main form">
    <tr>
      <th>{{mb_label object=$filter field="_date_min"}}</th>
      <td>{{mb_field object=$filter field="_date_min" form="stats_params" canNull="false" register=true}}</td>
      <th>{{mb_label object=$filterSejour field="type"}}</th>
      <td>
        <select name="type" style="width: 15em;">
          <option value="">&mdash; Tous les types d'hospi</option>
          {{foreach from=$filterSejour->_specs.type->_locales key=key_hospi item=curr_hospi}}
            <option value="{{$key_hospi}}" {{if $key_hospi == $filterSejour->type}}selected="selected"{{/if}}>
              {{$curr_hospi}}
            </option>
          {{/foreach}}
        </select>
      </td>
      <th>{{mb_label class=CSalle field="bloc_id"}}</th>
      <td>
        <select name="bloc_id" style="width: 15em;">
          <option value="">&mdash; {{tr}}CBlocOperatoire.all{{/tr}}</option>
          {{foreach from=$listBlocs item=curr_bloc}}
            <option value="{{$curr_bloc->_id}}" {{if $curr_bloc->_id == $bloc->_id }}selected="selected"{{/if}}>
              {{$curr_bloc->nom}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$filter field="_date_max"}}</th>
      <td>{{mb_field object=$filter field="_date_max" form="stats_params" canNull="false" register=true}} </td>

      <th>{{mb_label object=$filter field="_prat_id"}}</th>
      <td>
        <select name="prat_id" style="width: 15em;">
          <option value="">&mdash; Tous les praticiens</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$listPrats selected=$filter->_prat_id}}
        </select>
      </td>

      <th>{{mb_label object=$filter field="salle_id"}}</th>
      <td>
        <select name="salle_id" style="width: 15em;">
          <option value="">&mdash; {{tr}}CSalle.all{{/tr}}</option>
          {{foreach from=$listBlocsForSalles item=curr_bloc}}
            <optgroup label="{{$curr_bloc->nom}}">
              {{foreach from=$curr_bloc->_ref_salles item=curr_salle}}
                <option value="{{$curr_salle->_id}}" {{if $curr_salle->_id == $filter->salle_id}}selected="selected"{{/if}}>
                  {{$curr_salle->nom}}
                </option>
                {{foreachelse}}
                <option value="" disabled="disabled">{{tr}}CSalle.none{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th><label for="_complete_months" title="Mois complets">Mois complets</label></th>
      <td>
        <input type="checkbox" name="_complete_months_view"
               onchange="$V(this.form._complete_months, this.checked ? 1 : 0)" />
        <input type="hidden" name="_complete_months" value="0" />
      </td>
      <th>{{mb_label object=$filter field="_func_id"}}</th>
      <td>
        <select name="func_id" style="width: 15em;">
          <option value="">&mdash; Tous les cabinets</option>
          {{mb_include module=mediusers template=inc_options_function list=$listFuncs selected=$filter->_func_id}}
        </select>
      </td>
      <th></th>
      <td></td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field="codes_ccam"}}</th>
      <td>
        {{mb_field object=$filter field="codes_ccam" canNull="true" size="20"}}
        <button class="search" type="button" onclick="CCAMSelector.init()">Rechercher</button>
        <script type="text/javascript">
          CCAMSelector.init = function () {
            this.sForm = "stats_params";
            this.sView = "codes_ccam";
            this.sChir = "_chir";
            this.sClass = "_class";
            this.pop();
          }
        </script>
      </td>
      <th>{{mb_label object=$filter field="_specialite"}}</th>
      <td>
        <select name="discipline_id" style="width: 15em;">
          <option value="0">&mdash; Toutes les spécialités</option>
          {{foreach from=$listDisciplines item=curr_disc}}
            <option value="{{$curr_disc->discipline_id}}" {{if $curr_disc->discipline_id == $filter->_specialite }}selected{{/if}}>
              {{$curr_disc->_view}}
            </option>
          {{/foreach}}
        </select>
      </td>
      <th>
        <label>Hors plage</label>
      </th>
      <td>
        <input type="checkbox" name="hors_plage_view" {{if $hors_plage}}checked{{/if}}
               onchange="$V(this.form.hors_plage, this.checked ? 1 : 0)" />
        <input type="hidden" name="hors_plage" value="{{$hors_plage}}" />
      </td>
    </tr>
  </table>
</form>


<table class="main">
  <tr>
    <th colspan="2">
      <hr />
      Salle d'intervention
    </th>
  </tr>
  <tr>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Répartition du nombre d'interventions par salle
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('intervparsalle')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Répartition du nombre d'annulation le jour même par salle
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('opannulees')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
  </tr>
  <tr>
    <td class="button">
      <div class="small-info" style="text-align: center">
        Répartition du nombre d'interventions par praticien
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('intervparprat')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
    <td>
      <div class="small-info" style="text-align: center">
        Tableau d'occupation de salle par praticien
        <br />
        <button type="button" class="list"
                onclick="DisplayGraph.occupationSalleParPrat()">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
  </tr>
  <tr>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Durées totales d'occupation des blocs
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('occupationsalletotal')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Durées moyennes d'occupation des blocs
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('occupationsallemoy')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
  </tr>
  <tr>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Utilisation des ressources du bloc opératoire
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('ressourcesbloc')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Nombre de patients par jour et par salle
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('patjoursalle')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
  </tr>

  <tr>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        <strong>{{tr}}New{{/tr}}</strong>:
        Anticipation de la programmation des interventions
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('workflowoperation')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Liste des interventions au format CSV
        <br />
        <button type="button" class="download"
                onclick="DisplayGraph.listInterventions()">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
  </tr>
  <tr>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Délai de codage des interventions
        <br />
        <button type="button" class="list"
                onclick="DisplayGraph.codageQuality()">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Calcul des indicateurs de l'occupation des salles d'opération (ANAP)
        <br>
        <button type="button" class="list" onclick="DisplayGraph.displayANAP();">{{tr}}View{{/tr}}</button>
      </div>
    </td>
  </tr>
  <tr>
    <th colspan="2">
      <hr />
      SSPI
    </th>
  </tr>
  <tr>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Nombre moyen de patients par jour de la semaine en SSPI
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('patparjoursspi')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
    <td class="button" style="width: 50%">
      <div class="small-info" style="text-align: center">
        Nombre moyen de patients par heure de la journée en SSPI
        <br />
        <button type="button" class="stats"
                onclick="DisplayGraph.launchStats('patparheuresspi')">
          {{tr}}View{{/tr}}
        </button>
      </div>
    </td>
  </tr>
</table>