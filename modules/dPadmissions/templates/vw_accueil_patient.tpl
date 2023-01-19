{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admissions  script=admissions}}

<script>
  seeAccueilPatient = function() {
    var form = getForm('formAccueilPatient');
    new Url('admissions', 'vw_accueil_patient')
      .addFormData(form)
      .addParam("type_pec[]" , $V(form.elements['type_pec[]']), true)
      .addParam('only_list', 1)
      .addParam('only_list', 1)
      .addParam("date_interv_eg_entree", ($V(form._date_interv_eg_entree)) ? 1 : 0)
      .requestUpdate('see_accueil_patient_list');

  };

  sortByAccueil = function(order_col, order_way) {
    var form = getForm('formAccueilPatient');
    $V(form.order_col, order_col);
    $V(form.order_way, order_way);
    seeAccueilPatient();
  };

  reloadLineSejourAccueil = function(sejour_guid) {
    var url = new Url('admissions', 'vw_accueil_patient');
    url.addFormData(getForm('formAccueilPatient'));
    url.addParam('sejour_guid', sejour_guid);
    url.requestUpdate('line_'+sejour_guid);
  };

  selectServices = function (view, callback, show_np) {
    var url = new Url("hospi", "ajax_select_services");
    url.addParam("view", view);
    url.addParam("ajax_request", 0);
    url.addParam("show_np", show_np);
    url.requestModal(null, null, {maxHeight: "95%"});
  };

  Main.add(function(){
    seeAccueilPatient();

    $("see_accueil_patient_list").fixedTableHeaders();
  });
</script>
<form name="formAccueilPatient" method="get" action="?">
  <input type="hidden" name="order_col" value="{{$order_col}}"/>
  <input type="hidden" name="order_way" value="{{$order_way}}"/>
  <table class="form">
    <tr>
      <th class="title" colspan="10">
        <button type="button" class="lookup me-tertiary" style="float:right" onclick="Admissions.showAccueilPresentation(this)">
          {{tr}}admissions-presentation title{{/tr}}
        </button>
        {{tr}}mod-dPadmissions-tab-vw_accueil_patient{{/tr}}
      </th>
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$filter mb_field="_date_min_stat"}}
        {{mb_field object=$filter field="_date_min_stat" form="formAccueilPatient" register=true canNull="false"}}
      {{/me_form_field}}
      <th></th>
      <td><button type="button" name ="filter_sejours" onclick="Admissions.selectSejours('accueil');" class="search me-tertiary">{{tr}}admissions-action-Admission type{{/tr}}</button></td>
      {{me_form_field nb_cells=2 label="CSejour-_period_display"}}
        <select name="period">
          <option value=""      {{if !$period          }}selected{{/if}}>&mdash; {{tr}}dPAdmission.admission all the day{{/tr}}</option>
          <option value="matin" {{if $period == "matin"}}selected{{/if}}>{{tr}}dPAdmission.admission morning{{/tr}}</option>
          <option value="soir"  {{if $period == "soir" }}selected{{/if}}>{{tr}}dPAdmission.admission evening{{/tr}}</option>
        </select>
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$filter mb_field="_date_max_stat"}}
        {{mb_field object=$filter field="_date_max_stat" form="formAccueilPatient" register=true canNull="false"}}
      {{/me_form_field}}
      {{me_form_field nb_cells=2 mb_object=$filter mb_field=_statut_pec }}
        {{mb_field object=$filter field=_statut_pec emptyLabel="All"}}
      {{/me_form_field}}
      {{me_form_field nb_cells=2 mb_object=$filter mb_field=praticien_id }}
        <select name="praticien_id" style="width: 12em;">
          <option value="">&mdash; {{tr}}CMediusers.praticiens.all{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$prats selected=$filter->praticien_id}}
        </select>
      {{/me_form_field}}
    </tr>
    <tr>
      <td></td>
      <td colspan="2">
        <label title="{{tr}}admissions-Admissions intervention date is entry date{{/tr}}">
          <input type="checkbox" name="_date_interv_eg_entree">
          {{tr}}admissions-Admissions intervention date is entry date-short{{/tr}}
        </label>
      </td>
      <td>
        {{foreach from=$filter->_specs.type_pec->_list item=_type_pec}}
          <label>
            {{$_type_pec}} <input type="checkbox" name="type_pec[]" value="{{$_type_pec}}" checked/>
          </label>
        {{/foreach}}
      </td>
      <td></td>
      <td>
        <input type="checkbox" name="_active_filter_services" title="Prendre en compte le filtre sur les services"
               onclick="$V(this.form.active_filter_services, this.checked ? 1 : 0); this.form.filter_services.disabled = !this.checked;"
               {{if $enabled_service == 1}}checked{{/if}} />
        <input type="hidden" name="active_filter_services" value="{{$enabled_service}}"/>
        <button type="button" name ="filter_services" onclick="selectServices('accueilPatient', seeAccueilPatient, 1);" class="search me-tertiary" {{if $enabled_service == 0}}disabled{{/if}}>Services</button>
      </td>
    </tr>
    <tr>
      <td colspan="10" class="button">
        <button type="button" onclick="seeAccueilPatient();" class="search me-primary">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="see_accueil_patient_list" class="me-align-auto me-padding-0 me-bg-white">
  {{mb_include module=admissions template=vw_accueil_patient_list}}
</div>
