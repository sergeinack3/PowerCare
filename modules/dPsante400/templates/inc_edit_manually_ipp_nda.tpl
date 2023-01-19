{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=ipp value=$patient->_ref_IPP}}
{{assign var=nda value=$sejour->_ref_NDA}}

{{unique_id var=unique_ipp}}
{{unique_id var=unique_nda}}

<table class="form">
  <tr>
    <th class="title">Saisie manuelle</th>
  </tr>
  <tr>
    <td>
      {{mb_include module=dPsante400 template=inc_form_ipp_nda idex=$ipp object=$patient field=_IPP unique=$unique_ipp}}
    </td>
  </tr>
  <tr>
    <td>
      {{mb_include module=dPsante400 template=inc_form_ipp_nda idex=$nda object=$sejour field=_NDA unique=$unique_nda}}
    </td>
  </tr>
  <tr>
    {{if !$ipp->id400 || !$nda->id400}}
      <td class="button">
        <button type="button" class="save" onclick="Idex.submit_ipp_nda('{{$unique_nda}}', '{{$unique_ipp}}')">
          {{tr}}Save{{/tr}} & {{tr}}Close{{/tr}}
        </button>
      </td>
    {{/if}}
  </tr>
</table>