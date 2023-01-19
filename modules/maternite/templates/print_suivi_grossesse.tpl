{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=suivi_grossesse value=$consult->_ref_suivi_grossesse}}

<th class="category" colspan="4">
  {{tr}}CSuiviGrossesse{{/tr}}
</th>

<tr>
  <td colspan="2">
    <table class="form">
      <tr>
        <th class="halfPane">{{mb_label object=$suivi_grossesse field=type_suivi}}</th>
        <td>{{mb_value object=$suivi_grossesse field=type_suivi}}</td>
      </tr>
    </table>
  </td>
</tr>
<tr>
  <td class="halfPane">
    <fieldset>
      <legend>{{tr}}CSuiviGrossesse-exam_general{{/tr}}</legend>
      <table class="form me-no-box-shadow">
        {{assign var=first_in_line value=true}}
        {{foreach from=$suivi_grossesse_champs.exam_general key=_field item=_value}}
          {{if $first_in_line}}
            <tr>
          {{/if}}
          <th class="quarterPane">{{mb_label object=$suivi_grossesse field=$_field}}</th>
          <td>
            {{mb_value object=$suivi_grossesse field=$_field}}
          </td>
          {{if !$first_in_line}}
            </tr>
            {{assign var=first_in_line value=true}}
          {{else}}
            {{assign var=first_in_line value=false}}
          {{/if}}
          {{foreachelse}}
          <td class="empty">{{tr}}CSuiviGrossesse-exam_general.none{{/tr}}</td>
        {{/foreach}}
      </table>
    </fieldset>
    <fieldset>
      <legend>{{tr}}CSuiviGrossesse-exam_genico{{/tr}}</legend>
      <table class="form me-no-box-shadow">
        {{assign var=first_in_line value=true}}
        {{foreach from=$suivi_grossesse_champs.exam_genico key=_field item=_value}}
          {{if $first_in_line}}
            <tr>
          {{/if}}
          <th class="quarterPane">{{mb_label object=$suivi_grossesse field=$_field}}</th>
          <td>
            {{mb_value object=$suivi_grossesse field=$_field}}
          </td>
          {{if !$first_in_line}}
            </tr>
            {{assign var=first_in_line value=true}}
          {{else}}
            {{assign var=first_in_line value=false}}
          {{/if}}
          {{foreachelse}}
          <td class="empty">{{tr}}CSuiviGrossesse-exam_genico.none{{/tr}}</td>
        {{/foreach}}
      </table>
    </fieldset>
  </td>
  <td class="halfPane">
    <fieldset>
      <legend>{{tr}}CSuiviGrossesse-exam_comp{{/tr}}</legend>
      <table class="form me-no-box-shadow">
        {{assign var=first_in_line value=true}}
        {{foreach from=$suivi_grossesse_champs.exam_comp key=_field item=_value}}
          {{if $first_in_line}}
            <tr>
          {{/if}}
          <th class="quarterPane">{{mb_label object=$suivi_grossesse field=$_field}}</th>
          <td>
            {{mb_value object=$suivi_grossesse field=$_field}}
          </td>
          {{if !$first_in_line}}
            </tr>
            {{assign var=first_in_line value=true}}
          {{else}}
            {{assign var=first_in_line value=false}}
          {{/if}}
          {{foreachelse}}
          <td class="empty">{{tr}}CSuiviGrossesse-exam_comp.none{{/tr}}</td>
        {{/foreach}}
      </table>
    </fieldset>
    <fieldset>
      <legend>{{tr}}CSuiviGrossesse-functionnal_signs{{/tr}}</legend>
      <table class="form me-no-box-shadow">
        {{assign var=first_in_line value=true}}
        {{foreach from=$suivi_grossesse_champs.functionnal_signs key=_field item=_value}}
          {{if $first_in_line}}
            <tr>
          {{/if}}
          <th class="quarterPane">{{mb_label object=$suivi_grossesse field=$_field}}</th>
          <td>
            {{mb_value object=$suivi_grossesse field=$_field}}
          </td>
          {{if !$first_in_line}}
            </tr>
            {{assign var=first_in_line value=true}}
          {{else}}
            {{assign var=first_in_line value=false}}
          {{/if}}
          {{foreachelse}}
          <td class="empty">{{tr}}CSuiviGrossesse-functionnal_signs.none{{/tr}}</td>
        {{/foreach}}
      </table>
    </fieldset>
    <fieldset>
      <legend>{{mb_label object=$suivi_grossesse field=conclusion}}</legend>
      <table class="form me-no-box-shadow">
      {{if $suivi_grossesse->conclusion}}
        {{mb_value object=$suivi_grossesse field=conclusion}}
      {{else}}
        <td class="empty">{{tr}}CSuiviGrossesse.conclusion.{{/tr}}</td>
      {{/if}}
      </table>
    </fieldset>
  </td>
</tr>
