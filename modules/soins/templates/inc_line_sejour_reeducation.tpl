{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient      value=$_sejour->_ref_patient}}
{{assign var=prescription value=$_sejour->_ref_prescription_sejour}}

<tr>
  <td class="narrow">
    {{mb_include module=patients template=inc_vw_photo_identite size=32 nodebug=true}}
  </td>
  <td>
    {{mb_include module=ssr template=inc_view_patient
                 onclick="Sejour.showDossierSoinsModal(`$_sejour->_id`, null, {afterClose: Soins.reloadSejoursReeducation.curry('`$_sejour->_id`')});"}}
  </td>
  <td class="text compact">
    {{foreach from=$prescription->_ref_alertes item=_alerte}}
      <div>
        <button type="button" onclick="Soins.traiterAlerteReeducation('{{$_alerte->_id}}', '{{$_sejour->_id}}');"
                style="display: none;"
                class="tick notext compact alerte">{{tr}}Treat{{/tr}}</button>
        {{$_alerte->comments}}
      </div>
    {{/foreach}}
  </td>
  <td>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
      {{mb_value object=$_sejour field=entree}}
    </span>
  </td>
  <td>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
      {{mb_value object=$_sejour field=sortie}}
    </span>
  </td>
  <td>
    {{mb_include module=soins template=inc_cell_motif_sejour sejour=$_sejour}}
  </td>
  <td>
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
  </td>
  <td>
    <button type="button" class="new"
            onclick="Consultation.editRDVModal(null, null, null, null, null, null, '{{$_sejour->_id}}', Soins.reloadSejoursReeducation.curry('{{$_sejour->_id}}'));">
      {{tr}}CConsultation-title-create{{/tr}}
    </button>

    {{if $prescription->_ref_alertes|@count}}
      <button type="button" class="tick" onclick="this.up('tr').select('button.alerte').invoke('onclick');">{{tr}}Treat{{/tr}}</button>
    {{/if}}

    {{foreach from=$_sejour->_ref_consultations item=_consultation}}
      <div class="compact">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_consultation->_guid}}')">
          {{tr var1=$_consultation->_datetime|date_format:$conf.date}}CConsultation-Consultation on %s-court{{/tr}}
        </span>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consultation->_ref_praticien initials=border}}
      </div>
    {{/foreach}}
  </td>
</tr>