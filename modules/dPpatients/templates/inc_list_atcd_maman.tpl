{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  addAtcd = function (rques, type, appareil) {
    var form = getForm("editAntFrm");
    if (form) {
      $V(form.rques, rques);
      $V(form.type, type);
      $V(form.appareil, appareil);

      onSubmitAnt(form);
    }
  };
</script>

<h2>
  Antécédents de <span onmouseover="ObjectTooltip.createEx(this, '{{$maman->_guid}}')">{{$maman}}</span>
</h2>

<table class="tbl">
  {{foreach from=$maman->_ref_dossier_medical->_all_antecedents item=_antecedent}}
    <tr>
      <td>
        <button type="button" class="add notext oneclick"
                onclick="addAtcd('{{$_antecedent->rques|smarty:nodefaults|JSAttribute}}', '{{$_antecedent->type}}', '{{$_antecedent->appareil}}');"></button>
        <strong>
          {{if $_antecedent->type    }} {{mb_value object=$_antecedent field=type    }} {{/if}}
          {{if $_antecedent->appareil}} {{mb_value object=$_antecedent field=appareil}} {{/if}}
        </strong>
        {{if $_antecedent->date}}
          {{mb_value object=$_antecedent field=date}} :
        {{/if}}
        {{$_antecedent->rques|nl2br}}
      </td>
    </tr>
  {{/foreach}}
</table>