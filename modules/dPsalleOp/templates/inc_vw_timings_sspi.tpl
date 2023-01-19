{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=ambu value=0}}

{{if "brancardage"|module_active && "brancardage General use_brancardage"|gconf}}
  {{mb_script module=brancardage script=brancardage ajax=true}}
{{/if}}

{{mb_ternary var=submit test=isset($submitTimingSSPI|smarty:nodefaults) value=$submitTimingSSPI other=submitTimingSSPI}}
{{assign var=opid value=$operation->_id}}
{{assign var=form value=timingSSPI$opid}}
{{assign var=use_sortie_reveil_reel value="dPsalleOp COperation use_sortie_reveil_reel"|gconf}}

<form name="{{$form}}" method="post">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  {{mb_key object=$operation}}

  <table class="form">
    {{if $ambu}}
      <tr>
        <th class="category" colspan="2">{{tr}}module-ambu-timings sspi{{/tr}}</th>
      </tr>
      <tr>
        <th>
          {{mb_label object=$operation field=entree_reveil}}
        </th>
        <td>
          {{if $operation->sortie_reveil_possible}}
            {{mb_value object=$operation field=entree_reveil}}
          {{else}}
            {{mb_include module=salleOp template=inc_field_timing field=entree_reveil object=$operation show_label=0}}
          {{/if}}
        </td>
      </tr>
      <tr>
        <th>
          <label for="" title="{{tr}}COperation-sortie_reveil_possible-desc{{/tr}}">
            {{tr}}COperation-sortie_reveil_possible-court{{/tr}}
          </label>
        </th>
        <td>
          {{if $use_sortie_reveil_reel && $operation->sortie_reveil_reel}}
            {{mb_value object=$operation field=sortie_reveil_possible}}
          {{elseif $operation->entree_reveil}}
            {{mb_include module=salleOp template=inc_field_timing field=sortie_reveil_possible object=$operation show_label=0}}
          {{/if}}
        </td>
      </tr>
      {{if $use_sortie_reveil_reel}}
      <tr>
          <th>
            {{mb_label object=$operation field=sortie_reveil_reel}}
          </th>
          <td>
            {{if $operation->entree_reveil}}
              {{mb_include module=salleOp template=inc_field_timing field=sortie_reveil_reel object=$operation show_label=0}}
            {{else}}
              {{mb_value object=$operation field=sortie_reveil_possible}}
            {{/if}}
          </td>
      </tr>
      {{/if}}
    {{else}}
      <tr>
        <th class="category">
          {{mb_label object=$operation field=entree_reveil}}
        </th>
        <th class="category">
          {{mb_label object=$operation field=sortie_reveil_possible}}
        </th>
        {{if $use_sortie_reveil_reel}}
          <th class="category">
            {{mb_label object=$operation field=sortie_reveil_reel}}
          </th>
        {{/if}}
      </tr>
      <tr>
        <td>
          {{if $operation->sortie_reveil_possible}}
            {{mb_value object=$operation field=entree_reveil}}
          {{else}}
            {{mb_include module=salleOp template=inc_field_timing field=entree_reveil object=$operation}}
          {{/if}}
        </td>
        <td>
          {{if $use_sortie_reveil_reel && $operation->sortie_reveil_reel}}
            {{mb_value object=$operation field=sortie_reveil_possible}}
          {{elseif $operation->entree_reveil}}
            {{mb_include module=salleOp template=inc_field_timing field=sortie_reveil_possible object=$operation}}
          {{/if}}
        </td>
        <td>
          {{if $operation->entree_reveil}}
            {{mb_include module=salleOp template=inc_field_timing field=sortie_reveil_reel object=$operation}}
          {{else}}
            {{mb_value object=$operation field=sortie_reveil_possible}}
          {{/if}}
        </td>
      </tr>
    {{/if}}
  </table>
</form>