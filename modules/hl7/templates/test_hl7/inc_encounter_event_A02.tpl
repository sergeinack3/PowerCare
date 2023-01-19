{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshMouvements = function() {
    //compatibilité avec dpHospi

    Control.Modal.close();
  };

  changeLit = function(affectation_id, link_affectation, datetime) {
    var url = new Url('hospi', 'ajax_suggest_lit');
    url.addParam('affectation_id', affectation_id);
    url.addParam("datetime", datetime);
    if (link_affectation) {
      url.addParam("_link_affectation", link_affectation);
    }

    url.requestModal(700, 400);
  }
</script>

{{mb_include module=hl7 template=inc_banner_event_hl7}}

{{assign var="formName" value="test_hl7_event$event"}}

<form method="post" name="{{$formName}}" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="hl7">
  <input type="hidden" name="dosql" value="do_encounter_event">
  <input type="hidden" name="event" value="{{$event}}">
  <input type="hidden" name="patient_id" value="{{$patient->_id}}">
  <input type="hidden" name="callback" value="Control.Modal.close">
  <table class="form">
    {{foreach from=$patient->_ref_sejours item=_sejour}}
      <tr>
        <td>
          <label>
            <input type="radio" name="sejour_id" value="{{$_sejour->_id}}">
            {{$_sejour->_view}} [{{if $_sejour->_NDA}}{{$_sejour->_NDA}}{{else}}-{{/if}}]
          </label>
        </td>
        <td>
          {{if $_sejour->_ref_curr_affectation && $_sejour->_ref_curr_affectation->_id}}
            <button type="button" class="edit" onclick="Affectation.from_tempo=true; Affectation.edit('{{$_sejour->_ref_curr_affectation->_id}}')">
              {{tr}}CAffectation{{/tr}}
            </button>
          {{else}}
            {{mb_field object=$_sejour field=_unique_lit_id hidden=true onchange="this.form.onsubmit()"}}
            <input type="text" name="_unique_lit_id_view" style="width: 12em" value=""/>
            <script>
              Main.add(function(){
                var form = getForm("{{$formName}}");

                var url = new Url("system", "ajax_seek_autocomplete");
                url.addParam("object_class", "CLit");
                url.addParam("field", "lit_id");
                url.addParam("input_field", "_unique_lit_id_view");
                url.addParam("show_view", "true");
                url.addParam("where[lit.annule]", "0");
                url.autoComplete(form.elements._unique_lit_id_view, null, {
                  minChars: 2,
                  method: "get",
                  select: "view",
                  dropdown: true,
                  afterUpdateElement: function(field, selected){
                    var value = selected.id.split('-')[2];
                    $V(form._unique_lit_id, value);
                  },
                  callback: function(input, queryString){
                    var service_id = $V(form.service_id);
                    if (service_id) {
                      queryString += "&where[chambre.service_id]="+service_id;
                      queryString += "&ljoin[chambre]=chambre.chambre_id=lit.chambre_id";
                    }
                    return queryString;
                  }
                });
              });
            </script>
          {{/if}}
        </td>
      </tr>
    {{foreachelse}}
      <tr><td><span class="empty">{{tr}}CSejour.none{{/tr}}</span></td></tr>
    {{/foreach}}
  </table>
</form>