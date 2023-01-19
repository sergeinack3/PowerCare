{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  submitFields = function() {
    var oForm = getForm("editFieldsFrm");
    return onSubmitFormAjax(oForm);
  };

  redirectDHEPatient = function() {
    var oForm = getForm("editFieldsFrm");
    var url = new Url("planningOp", "dhe_externe");
    url.addParam("praticien_id"                 , '{{$praticien_id}}');
    url.addParam("patient_id"                   , $V(oForm.patient_id));
    {{if isset($sejour|smarty:nodefaults)}}
    url.addParam("sejour_libelle"               , '{{$sejour->libelle}}');
    url.addParam("sejour_type"                  , '{{$sejour->type}}');
    url.addParam("sejour_entree_prevue"         , '{{$sejour->entree_prevue}}');
    url.addParam("sejour_sortie_prevue"         , '{{$sejour->sortie_prevue}}');
    url.addParam("sejour_remarques"             , '{{$sejour->rques}}');
    url.addParam("sejour_intervention"          , '{{$sejour_intervention}}');
    {{/if}}
    {{if isset($intervention|smarty:nodefaults)}}
    url.addParam("intervention_date"            , '{{$intervention->_datetime}}');
    url.addParam("intervention_duree"           , '{{$intervention->temp_operation}}');
    url.addParam("intervention_cote"            , '{{$intervention->cote}}');
    url.addParam("intervention_horaire_souhaite", '{{$intervention->horaire_voulu}}');
    url.addParam("intervention_codes_ccam"      , '{{$intervention->codes_ccam}}');
    url.addParam("intervention_materiel"        , '{{$intervention->materiel}}');
    url.addParam("intervention_remarques"       , '{{$intervention->rques}}');
    {{/if}}
    url.redirect();
  };

  redirectDHESejour = function() {
    var oForm = getForm("editFieldsFrm");
    var url = new Url("planningOp", "dhe_externe");
    url.addParam("praticien_id"                 , $V(oForm.praticien_id));
    url.addParam("patient_id"                   , $V(oForm.patient_id));
    url.addParam("sejour_id"                    , $V(oForm.sejour_id));
    url.addParam("sejour_intervention"          , '{{$sejour_intervention}}');
    {{if isset($intervention|smarty:nodefaults)}}
    url.addParam("intervention_date"            , '{{$intervention->_datetime}}');
    url.addParam("intervention_duree"           , '{{$intervention->temp_operation}}');
    url.addParam("intervention_cote"            , '{{$intervention->cote}}');
    url.addParam("intervention_horaire_souhaite", '{{$intervention->horaire_voulu}}');
    url.addParam("intervention_codes_ccam"      , '{{$intervention->codes_ccam}}');
    url.addParam("intervention_materiel"        , '{{$intervention->materiel}}');
    url.addParam("intervention_remarques"       , '{{$intervention->rques}}');
    {{/if}}
    url.redirect();
  };

  changeField = function(sField, sValue) {
    var oForm = getForm("editFieldsFrm");
    $V(oForm[sField], sValue);
  };
</script>

<table class="main">
  <tr>
    <th class="title">Demande d'hospitalisation électronique externe</th>
  </tr>
  <tr>
    <td>
      {{if !$praticien_id}}
      <div class="error">
        Code praticien invalide
      </div>
      {{elseif $msg_error}}
      <div class="small-error">
        {{$msg_error|smarty:nodefaults}}
      </div>
      {{if isset($patient->_id|smarty:nodefaults) || isset($sejour->_id|smarty:nodefaults)}}
      <div class="small-info">
        Vous pouvez cependant effectuer les actions suivantes :
        <ul>
        {{if isset($sejour->_id|smarty:nodefaults)}}
          <li>
            <strong>Annuler ou modifier</strong>
            le {{$sejour->_view}} de {{$patient->_view}}
          </li>
          <li>
            <strong>Planifier une intervention</strong>
            au sein du {{$sejour->_view}} de {{$patient->_view}}
          </li>
          <li>
            <strong>Planifier une intervention hors plage</strong>
            au sein du {{$sejour->_view}} de {{$patient->_view}}
          </li>
        {{elseif isset($patient->_id|smarty:nodefaults)}}
          <li>
            <strong>Modifier le patient</strong>
            {{$patient->_view}}
          </li>
          <li>
            <strong>Planifier un séjour</strong>
            pour {{$patient->_view}}
          </li>
          <li>
            <strong>Planifier une intervention</strong>
            pour {{$patient->_view}}
          </li>
          <li>
            <strong>Planifier une intervention hors plage</strong>
            pour {{$patient->_view}}
          </li>
        {{/if}}
        </ul>
      </div>
      {{/if}}
      {{elseif isset($list_fields|smarty:nodefaults)}}
      <form name="editFieldsFrm" action="?" method="post" onsubmit="return onSubmitFormAjax(this, {{$list_fields.action}});">
      <input type="hidden" name="m" value="{{$list_fields.object->_ref_module->mod_name}}" />
      <input type="hidden" name="dosql" value="{{if $list_fields.object->_class == 'CPatient'}}do_patients_aed{{else}}do_sejour_aed{{/if}}" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="_purge" value="0" />
      {{mb_key object=$list_fields.object_existant}}
      <fieldset>
        <legend>Veuillez choisir les valeurs à conserver</legend>
        <table class="tbl">
          <tr>
            <th class="category narrow"></th>
            <th class="category" style="width: 33%">Résultat</th>
            <th class="category" style="width: 33%">Proposé</th>
            <th class="category" style="width: 33%">Existant</th>
          </tr>
          {{foreach from=$list_fields.fields key=_field item=_state}}
          <tr>
            <td style="text-align: right">{{mb_label object=$list_fields.object_resultat field=$_field}}</td>
            <td class="{{if $_state}}ok{{else}}warning{{/if}}">
              {{if $_state}}
              {{mb_field object=$list_fields.object_resultat field=$_field hidden="hidden" readonly="readonly"}}
              {{mb_value object=$list_fields.object_resultat field=$_field}}
              {{else}}
              {{mb_field object=$list_fields.object_resultat field=$_field readonly="readonly"}}
              {{/if}}
              
            </td>
            <td class="{{if $_state}}ok{{else}}warning{{/if}}">
              {{if !$_state}}
              <input type="radio" name="_choice_{{$_field}}" value="{{$list_fields.object->$_field}}" checked onchange="changeField('{{$_field}}', '{{$list_fields.object->$_field}}')" />
              {{/if}}
              {{mb_value object=$list_fields.object field=$_field}}
            </td>
            <td class="{{if $_state}}ok{{else}}warning{{/if}}">
              {{if !$_state}}
              <input type="radio" name="_choice_{{$_field}}" value="{{$list_fields.object_existant->$_field}}" onchange="changeField('{{$_field}}', '{{$list_fields.object_existant->$_field}}')" />
              {{/if}}
              {{mb_value object=$list_fields.object_existant field=$_field}}
            </td>
          </tr>
          <tr>
          {{/foreach}}
          <tr>
            <td></td>
            <td colspan="3" class="button">
              <button type="button" class="submit" onclick="this.form.onsubmit()">{{tr}}Submit{{/tr}}</button>
            </td>
          </tr>
        </table>
      </fieldset>
      </form>
      {{/if}}
    </td>
  </tr>
</table>
