{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=nb_consult value=0}}
{{mb_default var=load_js value=1}}

{{mb_script module=cabinet script=plage_consultation ajax=true}}
{{mb_script module=patients script=identity_validator ajax=true}}

{{if $load_js}}
  {{mb_script module=cabinet script=icone_selector ajax=true}}
  {{mb_script module=patients script=documentV2 ajax=true}}
{{/if}}

<script>
  // Notification de l'arrivée du patient
  if (!window.Consultations) {
    Consultations = {
      start: function() {
      window.location.reload();
      }
    };
  }

  putArrivee = function(oForm) {
    var today = new Date();
    oForm.arrivee.value = today.toDATETIME(true);
    onSubmitFormAjax(oForm, Consultations.start);
  };

  printPlage = function(plage_id) {
    new Url("cabinet", "print_plages")
      .addParam("plage_id", plage_id)
      .addParam("_telephone", 1)
      .popup(700, 550, "Planning");
  };

  if ($('tab-consultations')) {
    Control.Tabs.setTabCount('tab-consultations', {{$nb_consult}});
  }

  Main.add(function() {
    {{if "dPpatients CPatient manage_identity_vide"|gconf}}
      IdentityValidator.active = true;
    {{/if}}
  });
</script>

<table class="tbl me-no-align me-small">
  <tbody>
    {{foreach from=$listPlage item=_plage}}
      <tr>
        <th colspan="10" class="section" style="overflow: hidden">
          {{if $current_m == "dPurgences"}}
            <span style="float: right;">
              <button class="print notext me-tertiary" onclick="printPlage({{$_plage->_id}})">
                {{tr}}Print{{/tr}}
              </button>
            </span>
          {{/if}}
          {{mb_include module=system template=inc_object_notes object=$_plage}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_plage->_guid}}');">
            {{$_plage->debut|date_format:$conf.time}}
            - {{$_plage->fin|date_format:$conf.time}}
            {{if $_plage->libelle}}: {{$_plage->libelle|truncate:30:"..."}}{{/if}}
          </span>
        </th>
      </tr>
      {{foreach from=$_plage->_ref_consultations item=_consult}}
        {{mb_include module=cabinet template=inc_detail_consult}}
      {{foreachelse}}
        <tr>
          <td colspan="10" class="empty">{{tr}}CPlageconsult.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">{{tr}}CPlageconsult.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </tbody>
  <thead>
  {{if !$board}}
    <tr>
      <td colspan="10">
        {{if $canCabinet->read}}
          <script>
            Main.add(function() {
              Calendar.regField(getForm("changeView").date, null, {noView: true});
            });
          </script>
        {{/if}}
        <form name="changeView" method="get">
          <input type="hidden" name="m" value="{{$current_m}}" />
          <input type="hidden" name="tab" value="{{$tab}}" />
          <input type="hidden" name="selConsult" value="{{$consult->_id}}" />
          <input type="hidden" name="prat_id" value="{{$userSel->_id}}" />
          <table class="main layout">
            <tr>
              <td style="text-align: left; width: 100%; font-weight: bold; height: 20px;">
                <div class="me-no-display" style="float: right;">
                  {{if $current_date}}
                    {{$current_date|date_format:$conf.date}}
                  {{/if}}
                  {{$hour|date_format:$conf.time}}
                </div>
                {{$date|date_format:$conf.longdate}}
                {{if $canCabinet->read}}
                  <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
                {{/if}}
              </td>
            </tr>
            <tr>
              {{if $canCabinet->read}}
                <td>
                  <select name="vue2" onchange="ListConsults.toggleFinishedConsult(this.form)" style="width: 15em;">
                    <option value="0" {{if $vue == "0"}}selected{{/if}}>{{tr}}common-action-Display all{{/tr}}</option>
                    <option value="1" {{if $vue == "1"}}selected{{/if}}>{{tr}}common-action-Hide completed{{/tr}}</option>
                  </select>
                </td>
              {{/if}}
            </tr>
          </table>
        </form>
      </td>
    </tr>
  {{/if}}
  <tr>
    <th id="inc_list_consult_th_consult" class="title" colspan="10">
      {{mb_default var=date value=""}}
      {{mb_default var=print_content_class value=""}}
      {{mb_default var=print_content_id value=""}}
      {{assign var=print_plage_ids value='Ox\Core\CMbArray::pluck'|static_call:$listPlage:"_id"}}
      <button class="print notext me-tertiary" style="float: right;"
              {{if $date !== "" && $print_content_class !== "" && $print_content_id !== ""}}
                  onclick="PlageConsultation.printConsult('{{$date}}', '{{$print_content_class}}', '{{$print_content_id}}');"
              {{else}}
                  onclick="PlageConsultation.printPlages('{{"|"|implode:$print_plage_ids}}')"
              {{/if}}
              >
        {{tr}}Print{{/tr}}
      </button>
      {{tr}}CConsultation|pl{{/tr}}
      {{if $board && !$boardItem && isset($withClosed|smarty:nodefaults)}}
        <label style="float: right; font-size: 0.8em;">
          {{tr}}common-action-Hide completed{{/tr}}
          <input name="withClosed" type="checkbox" {{if !$withClosed}}checked{{/if}} onchange="updateListConsults(this.checked?0:1);"/>
        </label>
      {{/if}}
    </th>
  </tr>
  <tr>
    <th style="width: 50px; ">{{tr}}Hour{{/tr}}</th>
    <th colspan="10">{{tr}}CConsultation-patient-motif{{/tr}}</th>
  </tr>
  </thead>
</table>
