{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Tdb.views.filterByText('consultations_tab');
  });
</script>

<table class="tbl" id="consultations_tab">
  <tbody>
  {{foreach from=$listConsults item=_consult}}
    <tr>
      <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}');">
            {{mb_value object=$_consult field=heure}}
          </span>
        {{if $_consult->chrono == 16}}
          <button type="button" class="tick notext" onclick="Tdb.changeStatusConsult('{{$_consult->_id}}', '32');">
            {{tr}}CConsultation-notify_arrive-court{{/tr}}
          </button>
        {{elseif $_consult->chrono == 32}}
          <button type="button" class="tick_cancel notext" onclick="Tdb.changeStatusConsult('{{$_consult->_id}}', '16');">
            {{tr}}CConsultation-cancel_arrive{{/tr}}
          </button>
        {{/if}}
      </td>
      <td class="text">
          <span class="CPatient-view"
                onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_ref_grossesse->_ref_parturiente->_guid}}');">
            {{mb_value object=$_consult->_ref_grossesse field=parturiente_id}}
          </span>

        {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_consult->_ref_grossesse->_ref_parturiente}}
      </td>
      <td>
        {{$_consult->_ref_grossesse->_semaine_grossesse}} SA +{{$_consult->_ref_grossesse->_reste_semaine_grossesse}} j
      </td>
      <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_ref_plageconsult->_ref_chir->_guid}}');">
            {{mb_value object=$_consult->_ref_plageconsult field=chir_id}}
          </span>
      </td>
      <td>
        <button class="clock notext me-tertiary" onclick="Tdb.editRdvConsult('{{$_consult->_id}}');">{{tr}}Edit{{/tr}}</button>
        <button class="consultation notext" onclick="Tdb.editConsult('{{$_consult->_id}}');">{{tr}}Edit{{/tr}}</button>
        <button class="grossesse notext"
                onclick="Tdb.editGrossesse('{{$_consult->_ref_grossesse->_id}}');">{{tr}}CGrossesse.edit{{/tr}}</button>
        {{*<button class="consultation_create notext" onclick="Tdb.editConsult(0, '{{$_consult->_ref_grossesse->_id}}', '{{$_consult->_ref_grossesse->parturiente_id}}');">Nouvelle consultation</button>
        <button class="sejour_create notext" onclick="Tdb.editSejour(0, '{{$_consult->_ref_grossesse->_id}}','{{$_consult->_ref_grossesse->parturiente_id}}');">Nouveau séjour</button>
        <button class="accouchement_create notext" onclick="Tdb.editAccouchement(0, '{{$_consult->sejour_id}}','{{$_consult->_ref_grossesse->_id}}', '{{$_consult->_ref_grossesse->parturiente_id}}');">Nouvel accouchement</button>*}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">{{tr}}CConsultation.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  </tbody>
  <thead>
  <tr>
    <th class="title" colspan="10">
      <button style="float:left;" class="consultation_create notext"
              onclick="Tdb.editRdvConsult();">{{tr}}CConsultation-action-Make an consultation appointment{{/tr}}</button>
      <button type="button" class="change notext me-tertiary" onclick="Tdb.views.listConsultations(false);" style="float: right;">
        {{tr}}Refresh{{/tr}}
      </button>
      {{if $app->_ref_user->isSageFemme()}}
        <input class="me-float-right" type="checkbox" id="show_all_consult" name="show_all_consult"
               {{if $show_all_consult }}
                   checked
               {{/if}}
               onchange="Tdb.views.listConsultations(false, this);" >
        <label class="me-float-right" for="show_all_consult">{{tr}}mod-maternite-Display all consult{{/tr}}</label>
      {{/if}}
      <a onclick="zoomViewport(this);">{{if $listConsults|@count}}{{$listConsults|@count}}{{else}}Aucune{{/if}}
        Consultation{{if $listConsults|@count > 1}}s{{/if}} le {{$date|date_format:$conf.date}}</a>
    </th>
  </tr>
  <tr>
    <th class="narrow">{{mb_title class=CConsultation field=heure}}</th>
    <th>{{mb_title class=CGrossesse field=parturiente_id}}</th>
    <th>{{mb_title class=CGrossesse field=_semaine_grossesse}}</th>
    <th>{{mb_title class=CConsultation field=_praticien_id}}</th>
    <th class="narrow">{{tr}}Action{{/tr}}</th>
  </tr>
  </thead>
</table>
