{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=edit value=true}}

<script>
  Main.add(function() {
    TopHoraire.setupTopHoraire('{{$timing}}', '{{$operation->$timing}}');
  });
</script>

{{assign var=form value="editTopHoraire-`$operation->_id`-`$timing`"}}

{{if $edit}}
<form name="{{$form}}" method="post"
      onsubmit="return onSubmitFormAjax(this, TopHoraire.refresh.curry('{{$timing}}').bind(TopHoraire));">
  <input type="hidden" name="m"     value="planningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  {{mb_key object=$operation}}
  <input type="hidden" name="_set_{{$timing}}" value="1" />
{{/if}}

  {{assign var=timing_title value=$timing}}

  {{if $timing === "sortie_reveil_reel"}}
    {{assign var=timing_title value="sortie_sans_sspi"}}
  {{/if}}

  <table class="main" style="height: 100%;">
    <tr>
      <td style="width: 40px;">
        <i class="fas fa-{{if !$edit}}lock{{elseif $operation->$timing}}check-circle{{else}}save{{/if}}"></i>
      </td>
      <td class="text">
        <div>
          {{tr}}COperation-{{$timing_title}}{{/tr}}

          {{if $timing === "fin_op" && $operation->$timing && $edit}}
            {{mb_include module=forms template=inc_widget_ex_class_register object=$operation event_name=fin_intervention cssStyle="display: inline-block;"}}
          {{/if}}
        </div>
        {{if $edit}}
          {{if $operation->$timing}}
            <div style="font-size: 0.9em;">
              {{mb_field object=$operation field=$timing form=$form register=true onchange="this.form.onsubmit();"}}
            </div>
          {{else}}
            <input type="hidden" name="{{$timing}}" value="current" />
            {{if $timing === "sortie_reveil_reel"}}
              <input type="hidden" name="entree_reveil" value="current" />
              <input type="hidden" name="sortie_reveil_possible" value="current" />
            {{/if}}
          {{/if}}
        {{else}}
          {{mb_value object=$operation field=$timing}}
        {{/if}}
      </td>
    </tr>
  </table>
{{if $edit}}
</form>
{{/if}}