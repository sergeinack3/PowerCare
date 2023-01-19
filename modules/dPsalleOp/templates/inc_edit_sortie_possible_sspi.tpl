{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=ambu value=0}}

{{assign var=use_sortie_reveil_reel value="dPsalleOp COperation use_sortie_reveil_reel"|gconf}}

{{if !$use_sortie_reveil_reel}}
  <div class="small-info">
    La configuration permettant de saisir la sortie SSPI possible n'est pas activée. Veuillez contacter un administrateur.
  </div>

  {{mb_return}}
{{/if}}

{{assign var=submit value=submitSortiePossibleSSPI}}
{{assign var=opid value=$operation->_id}}
{{assign var=form value=sortiePossible$opid}}

<form name="{{$form}}" method="post">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  {{mb_key object=$operation}}

  <table class="{{if !$ambu}}tbl{{/if}} form">
    <tr>
      <th class="category" {{if $ambu}}colspan="2"{{/if}}>{{tr}}module-ambu-timings sspi{{/tr}}</th>
    </tr>
    {{if !$ambu}}
      {{mb_include module=salleOp template=inc_field_timing field=sortie_reveil_possible object=$operation}}
    {{else}}
      {{assign var=submit value="Ambu.submitSortiePossibleSSPI"}}
    <tr>
      <th>
        <span title="{{tr}}COperation-sortie_reveil_possible-desc{{/tr}}" style="font-weight: bold;">
          {{tr}}COperation-sortie_reveil_possible-court{{/tr}}
        </span>
      </th>
      <td>
        {{mb_include module=salleOp template=inc_field_timing field=sortie_reveil_possible object=$operation show_label=0}}
      </td>
    </tr>
    {{/if}}
  </table>
</form>