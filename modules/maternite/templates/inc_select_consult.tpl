{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{tr var1=$dnow|date_format:$conf.date}}CConsultation-Can choose consult or continue creating{{/tr}}
</div>

<table class="tbl">
  {{foreach from=$consults item=_consult}}
    <tr>
      <td>
        <button type="button" class="tick notext"
                {{if $_consult->_can->edit}}
                  onclick="Control.Modal.close(); Control.Modal.close(); Consultation.editModal('{{$_consult->_id}}', null, null, Placement.refreshCurrPlacement);"
                {{else}}
                  disabled
                {{/if}}></button>

        {{mb_value object=$_consult field=heure}} &mdash; {{$_consult->_ref_praticien->_view}}
      </td>
    </tr>
  {{/foreach}}

  <tr>
    <td class="button">
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
    </td>
  </tr>
</table>