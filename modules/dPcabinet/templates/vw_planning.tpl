{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=plage_consultation}}
{{mb_script module=cabinet script=edit_consultation}}
{{mb_script module=patients script=identity_validator}}

<script>
  // default value
  var target_plage_consult = '{{$plageSel->_id}}';

  Consultation.onCloseEdit = Consultation.onCloseEditModal = function() {
    if (target_plage_consult) {
      refreshPlageConsult(target_plage_consult);
    }
  };

  showConsultations = function (oTd, plageconsult_id){
    oTd = $(oTd);
    oTd.up("table").select(".event").invoke("removeClassName", "selected");
    oTd.up(".event").addClassName("selected");
    refreshPlageConsult(plageconsult_id);
    target_plage_consult = plageconsult_id;
  };

  refreshPlageConsult = function (plageconsult_id) {
    if (!plageconsult_id) {
      plageconsult_id = target_plage_consult;
    }
    target_plage_consult = plageconsult_id;
    var oform = getForm("selectPrat");

    //refresh the target
    var url = new Url("cabinet", "inc_consultation_plage");
    url.addParam("plageconsult_id", target_plage_consult);
    url.addParam("show_payees", $V(oform.show_payees));
    url.addParam("show_annulees", $V(oform.show_annulees));
    url.requestUpdate('consultations');
  };

  putArrivee =function (oForm) {
    var today = new Date();
    oForm.arrivee.value = today.toDATETIME(true);
    oForm.submit();
  };

  cancelArrivee = function(oForm) {
    oForm.submit();
  };

  undoCancellation = function(oForm) {
    oForm.submit();
  };

  cancelRdv = function(oForm) {
    Modal.open('form-motif_annulation-'+oForm.consultation_id.value);
  };

  goToDate = function (oForm, date) {
    $V(oForm.debut, date);
  };

  showConsultSiDesistement = function (){
    var url = new Url("cabinet", "vw_list_consult_si_desistement");
    url.addParam("chir_id", '{{$chirSel}}');
    url.requestModal();
  };

  printPlanning =function () {
    var url = new Url("cabinet", "print_planning");
    url.addParam("date", "{{$debut}}");
    url.addParam("chir_id", "{{$chirSel}}");
    url.popup(900, 600, "Planning");
  };

  Main.add(function () {
    {{if "dPpatients CPatient manage_identity_vide"|gconf}}
      IdentityValidator.active = true;
    {{/if}}

    var planning = window["planning-{{$planning->guid}}"];
    Calendar.regField(getForm("changeDate").debut, null, {noView: true});

    {{if $plageSel->_id}}
      var plageList =  $$(".{{$plageSel->_guid}}");
      if (plageList.length > 0) {
        showConsultations(plageList[0].down("a"), "{{$plageSel->_id}}");
      }
    {{/if}}

    // Autocomplete des praticiens
    var form = getForm("changePrat");
    var url = new Url("mediusers", "ajax_users_autocomplete");
    url.addParam("edit", '1');
    url.addParam("rdv", '1');
    url.addParam("input_field", 'chir_id_view');
    url.autoComplete(form.chir_id_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        if ($V(form.chir_id_view) == "") {
          $V(form.chir_id_view, selected.down('.view').innerHTML);
        }
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.chirSel, id);
      }
    });
  });
</script>

{{mb_script module=cabinet script=plage_consultation}}
{{mb_script module=ssr script=planning}}

<table class="main">
  <tr>
    <th style="width: 50%;">
      <form action="?" name="changePrat" method="get" style="float:left;text-align:left;width:15em;">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <input type="hidden" name="plageconsult_id" value="0" />
        <input type="hidden" name="chirSel" value="{{$chirSel}}" onchange="this.form.submit()"/>
        <input type="text" name="chir_id_view" class="autocomplete" value="{{if $chirSel}}{{$planning->title}}{{/if}}"
               onmousedown="$V(this, '');" onblur="$V(this, '{{$planning->title}}');"
               placeholder="&mdash; Choisir un praticien"/>
      </form>
      <form action="?" name="changeDate" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <input type="hidden" name="plageconsult_id" value="0" />
        <a href="#1" id="vw_planning_a_semaine" onclick="$V($(this).getSurroundingForm().debut, '{{$prec}}')">&lt;&lt;&lt;</a>

        Semaine du {{$debut|date_format:$conf.longdate}} au {{$fin|date_format:$conf.longdate}}
        <input type="hidden" name="debut" class="date" value="{{$debut}}" onchange="this.form.submit()" />

        <a href="#1" onclick="$V($(this).getSurroundingForm().debut, '{{$suiv}}')">&gt;&gt;&gt;</a>
        <br />
        <a href="#1" onclick="$V($(this).getSurroundingForm().debut, '{{$today}}')">Aujourd'hui</a>
      </form>
      <br/>
      <br/>
      {{if $canEditPlage}}
        <button style="float: left;" class="new me-primary" id="create_plage_consult_button" onclick="PlageConsultation.edit('0', '{{$debut}}');">{{tr}}CPlageconsult-title-create{{/tr}}</button>
      {{/if}}
    </th>
    <td style="min-width: 350px;">
      <button style="float: right;" class="print me-tertiary me-dark" onclick="printPlanning();">{{tr}}Print{{/tr}}</button>

      <div style="float: right; position:relative;">
        <button class="search me-tertiary me-dark" onclick="$('legend_planning').toggle();">{{tr}}Legend{{/tr}}</button>
        <div style="display: none; position:absolute; box-shadow: 0 0 3px grey; top: 20px; right:0; width:150px; border:solid 1px black; background-color: white;" id="legend_planning">
          <table style="width:100%;">
            <tr>
              <td style="background-color: rgb(221, 221, 221); width:30px;"></td>
              <td>Plage de consultation</td>
            </tr>
            <tr>
              <td style="background-color: #3E9DF4"></td>
              <td>Plage remplacée par un autre praticien</td>
            </tr>
            <tr>
              <td style="background-color: rgb(238, 221, 204)"></td>
              <td>Plage pour le compte d'un autre praticien</td>
            </tr>

            <tr>
              <td style="background-image: url('images/icons/ray_blue.gif'); background-color:  rgb(221, 221, 221)"></td>
              <td>Plage accessible par un tiers</td>
            </tr>
          </table>
        </div>
      </div>

      {{if $app->user_prefs.dPcabinet_offline_mode_frequency}}
        <button style="float: right;" class="download me-tertiary" onclick="PlageConsultation.downloadBackup();">{{tr}}common-Backup{{/tr}}</button>
      {{/if}}

      <button style="float: right;" class="download me-tertiary" onclick="Consultation.downloadPlanningCSV({{$chirSel}});">{{tr}}CConsultation-action-Download planning{{/tr}}</button>

      {{if $chirSel && $chirSel != -1}}
        <button type="button" class="lookup"
                {{if !$count_si_desistement}} disabled {{/if}}
                onclick="showConsultSiDesistement()">
          {{tr}}CConsultation-si_desistement{{/tr}} ({{$count_si_desistement}})
        </button>
        <!-- <button class="new" type="button" onclick="CreneauConsultation.modalPriseRDVTimeSlot('{{$chirSel}}', '', 0);">
          {*{{tr}}CPlageconsult-action-Next available time slot|pl{{/tr}}*}
        </button> -->
      {{/if}}
      <form action="?" name="selectPrat" method="get">
        <p>Afficher les :
          <label>
            <input type="checkbox" name="_show_payees" onchange="$V(this.form.show_payees, this.checked ? 1 : 0); refreshPlageConsult();" {{if $show_payees}}checked="checked"{{/if}}> payées
            <input type="hidden" name="show_payees" value="{{$show_payees}}" />
          </label>
          <label>
            <input type="checkbox" name="_show_annulees" onchange="$V(this.form.show_annulees, this.checked ? 1 : 0); refreshPlageConsult();" {{if $show_annulees}}checked="checked"{{/if}}> annulées
            <input type="hidden" name="show_annulees" value="{{$show_annulees}}" />
          </label>
        </p>
      </form>
    </td>
  </tr>
  <tr>
    <td>
      <div id="planning-plages">
        {{mb_include module=system template=calendars/vw_week}}
        <script type="text/javascript">

          redirectRDV = function(plageconsult_id) {
            var url = new Url('cabinet', 'edit_planning', 'tab');
            url.addParam('consultation_id', '');
            url.addParam('plageconsult_id', plageconsult_id);
            url.redirectOpener();
          };

        Main.add(function() {
          ViewPort.SetAvlHeight("planning-plages", 1);

          var planning = window['planning-{{$planning->guid}}'];

          // click on main div, refresh the consult_id
          $(planning).events.each(function(elt) {
            var target_element = $(elt.internal_id);
            target_element.setStyle({'cursor': 'pointer'});
            target_element.observe('click', function(event) {
              if (event.element().hasClassName('event')) {
                var button = target_element.down('.button.list');
                showConsultations(button, elt.guid.split("-")[1]);
              }
            })
          });

          planning.onMenuClick = function(event, plage, elem){
            if (event == 'list') {
              showConsultations(elem, plage);
            }

            if (event == 'edit') {
              PlageConsultation.edit(plage, '{{$debut}}');
            }

            if (event == 'clock') {
              redirectRDV(plage);
            }
          };

          // Lancer le calcul du view planning avec la hauteur height
          var height = $('planning-plages').getDimensions().height;
          planning.setPlanningHeight(height);
          planning.scroll();
        });

        </script>
      </div>
    </td>
    <td id="consultations">
      {{if !$plageSel->_id}}
        {{mb_include module=cabinet template=inc_consultations}}
      {{/if}}
    </td>
  </tr>
</table>
