{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=ccam_selector}}
{{mb_script module=pmsi script=printing}}


<script type="text/javascript">

function changeDate(sMin, sMax){
  var form = getForm("paramFrm");
  $V(form._date_min, sMin);
  $V(form._date_max, sMax);
  $V(form._date_min_da, Date.fromDATE(sMin).toLocaleDate());
  $V(form._date_max_da, Date.fromDATE(sMax).toLocaleDate());
}

function changeDateCal(form) {
  form.select_days.checked = false;

}
</script>


<form name="paramFrm" action="?m=pmsi" method="post" onsubmit="return Printing.checkPrint(this)">
<input type="hidden" name="_class" value="COperation" />
<input type="hidden" name="_chir" value="{{$chir}}" />
<table class="main">
  <tr>
    <td>
      <table class="form">
        <tr>
          <th class="category" colspan="3">Choix de la période</th>
        </tr>
        <tr>
          <th>{{mb_label object=$filter field="_date_min"}}</th>
          <td>{{mb_field object=$filter field="_date_min" form="paramFrm" canNull="false" onchange="changeDateCal(this.form)" register=true}} </td>
          <td rowspan="2">
            <input type="radio" name="select_days" onclick="changeDate('{{$yesterday}}','{{$yesterday}}');" value="yesterday" />
            <label for="select_days_yesterday">Jour précédent</label>
            <br />
            <input type="radio" name="select_days" onclick="changeDate('{{$now}}','{{$now}}');"  value="day" checked="checked" />
            <label for="select_days_day">{{tr}}Current-day{{/tr}}</label>
          </td>
        </tr>
        <tr>
           <th>{{mb_label object=$filter field="_date_max"}}</th>
           <td>{{mb_field object=$filter field="_date_max" form="paramFrm" canNull="false" onchange="changeDateCal(form)" register=true}} </td>
        </tr>
      </table>

    </td>
    <td>

      <table class="form">
        <tr>
          <th class="category" colspan="2">Choix des filtres</th>
        </tr>

        <tr>
          <th>{{mb_label object=$filter field=_ranking}}</th>
          <td>{{mb_field object=$filter field=_ranking emptyLabel=All typeEnum=radio}}</td>
        </tr>

        <tr>
          <th>{{mb_label object=$filter field=_cotation}}</th>
          <td>{{mb_field object=$filter field=_cotation emptyLabel=All typeEnum=radio}}</td>
        </tr>

        <tr>
          {{me_form_field animated=false nb_cells=2 mb_object=$filter mb_field="_prat_id" use_mb_label=1}}
            <select name="_prat_id">
              <option value="0">&mdash; {{tr}}common-all|pl{{/tr}}</option>
              {{mb_include module=mediusers template=inc_options_mediuser list=$listPrat}}
            </select>
          {{/me_form_field}}
        </tr>

        <tr>
          {{me_form_field animated=false nb_cells=2 mb_object=$filter mb_field="_specialite" use_mb_label=1}}
            <select name="_specialite">
              <option value="0">&mdash; {{tr}}common-all|f|pl{{/tr}}</option>
              {{mb_include module=mediusers template=inc_options_function list=$listSpec}}
            </select>
          {{/me_form_field}}
        </tr>

        <tr>
          {{me_form_field animated=false nb_cells=2 mb_object=$filter mb_field="salle_id" use_mb_label=1}}
            <select name="salle_id">
              <option value="0">&mdash; {{tr}}common-all|f|pl{{/tr}}</option>
              {{foreach from=$listBlocs item=_bloc}}
              <optgroup label="{{$_bloc}}">
                {{foreach from=$_bloc->_ref_salles item=_salle}}
                <option value="{{$_salle->_id}}" {{if $_salle->_id == $filter->salle_id}}selected="selected"{{/if}}>
                  {{$_salle->nom}}
                </option>
                {{foreachelse}}
                <option value="" disabled="disabled">{{tr}}CSalle.none{{/tr}}</option>
                {{/foreach}}
              </optgroup>
              {{/foreach}}
            </select>
          {{/me_form_field}}
        </tr>

        <tr>
          {{me_form_field animated=false nb_cells=2 mb_object=$filterSejour mb_field="type" use_mb_label=1}}
            {{mb_field object=$filterSejour field="type" canNull=true emptyLabel="All"}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field animated=false nb_cells=2 mb_object=$filterSejour mb_field="ald" use_mb_label=1}}
            {{mb_field object=$filterSejour field="ald" typeEnum=select emptyLabel="All"}}
          {{/me_form_field}}
        </tr>

        <tr>
          {{me_form_field nb_cells=2 mb_object=$filter mb_field="_codes_ccam" use_mb_label=1}}
            <input type="text" name="_codes_ccam" size="10" value="" />
            <button type="button" class="search" onclick="CCAMSelector.init()">sélectionner un code</button>
            <script type="text/javascript">
              CCAMSelector.init = function(){
                this.sForm  = 'paramFrm';
                this.sClass = '_class';
                this.sChir  = '_chir';
                this.sView  = '_codes_ccam';
                this.pop();
              };
              Main.add(function() {
                var oForm = getForm('paramFrm');
                var url = new Url('ccam', 'autocompleteCcamCodes');
                url.autoComplete(oForm._codes_ccam, '', {
                  minChars: 1,
                  dropdown: true,
                  width: '250px',
                  updateElement: function(selected) {
                    $V(oForm._codes_ccam, selected.down('strong').innerHTML);
                  }
                });
              });
            </script>
          {{/me_form_field}}
        </tr>

      </table>

    </td>
  </tr>
  <tr>
    <td colspan="2">
      <table class="form">
        <tr>
          <th class="category" colspan="2">Paramètres d'affichage</th>
        </tr>
        {{assign var="class" value="CPlageOp"}}

        <tr>
          <th style="width: 50%">{{mb_label object=$filter field="_plage"}}</th>
          <td>
            {{assign var="var" value="plage_vide"}}
            <label for="_plage">Oui</label>
            <input type="radio" name="_plage" value="1" {{if $conf.dPbloc.$class.$var == "1"}}checked="checked"{{/if}}/>
            <label for="_plage">Non</label>
            <input type="radio" name="_plage" value="0" {{if $conf.dPbloc.$class.$var == "0"}}checked="checked"{{/if}}/>
          </td>
        </tr>
         <tr>
          <th>{{mb_label object=$filter field="_ccam_libelle"}}</th>
          <td>
            {{assign var="var" value="libelle_ccam"}}
            <label for="_ccam_libelle">Oui</label>
            <input type="radio" name="_ccam_libelle" value="1" {{if $conf.dPbloc.$class.$var == "1"}}checked="checked"{{/if}}/>
            <label for="_ccam_libelle">Non</label>
            <input type="radio" name="_ccam_libelle" value="0" {{if $conf.dPbloc.$class.$var == "0"}}checked="checked"{{/if}}/>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <table class="form"><tr><td class="button"><button class="print" type="button" onclick="Printing.checkPrint(this.form)">Afficher</button></td></tr></table>
    </td>
  </tr>
</table>
</form>
