{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !array_key_exists($category_id, $lines) || !$lines.$category_id|@count}}
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  {{mb_return}}
{{/if}}

{{foreach from=$lines.$category_id item=_lines}}
  {{assign var=only_comment value=false}}
  {{if $_lines|@count > 1}}
    {{assign var=only_comment value=true}}
  {{/if}}
  {{foreach from=$_lines item=_line name="lines_prescription"}}
    {{if $_lines|@count > 1 && $smarty.foreach.lines_prescription.first}}
    <tr>
      <th>
        {{if $can_edit_prescription}}
        <button class="add notext" onclick="duplicateSSRLine('{{$_line->element_prescription_id}}', '{{$category_id}}');">
          {{tr}}Add{{/tr}}
        </button>
        {{/if}}
      </th>
      <td class="text" style="vertical-align: middle;">
        <span class="mediuser" style="border-left-color: #{{$_line->_ref_element_prescription->_color}};">
          <strong onmouseover="ObjectTooltip.createEx(this, '{{$_line->_guid}}');">
            {{$_line}}
          </strong>
        </span>
      </td>
    </tr>
    {{/if}}

    {{if $_line->_id == $full_line_id}}
    <tr>
      <td></td>
      <td  class="text" style="border: 1px solid #aaa;">
        <form name="editLine-{{$_line->_id}}" method="post">
          <input type="hidden" name="m" value="prescription" />
          <input type="hidden" name="dosql" value="do_prescription_line_element_aed" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="prescription_line_element_id" value="{{$_line->_id}}" />

          {{if $can_edit_prescription}}
          <button style="float: right" type="button" class="trash notext"
            onclick="$V(this.form.del, '1');
                     return onSubmitFormAjax(this.form, updateListLines.curry('{{$category_id}}', '{{$_line->prescription_id}}', ''));">
          </button>
          {{/if}}

          <button type="button" class="lock notext me-primary"
                  onclick="updateListLines('{{$category_id}}', '{{$_line->prescription_id}}', '');">
            {{tr}}Lock{{/tr}}
          </button>

          {{if $_lines|@count == 1}}
          <span class="mediuser" style="border-left-color: #{{$_line->_ref_element_prescription->_color}};">
            <strong onmouseover="ObjectTooltip.createEx(this, '{{$_line->_guid}}');"> {{$_line}}</strong>
          </span>
          {{/if}}

          <br />
          {{mb_label object=$_line field="commentaire"}}
          {{if $can_edit_prescription}}
            {{mb_field object=$_line field="commentaire" style="width: 20em;" onchange="onSubmitFormAjax(this.form);" multiline=true}}
          {{else}}
            {{mb_value object=$_line field="commentaire"}}
          {{/if}}
          <br />
          {{if $can_edit_prescription}}
            {{mb_label object=$_line field=debut}}
            {{mb_field object=$_line field=debut      form=editLine-$full_line_id register=true onchange="onSubmitFormAjax(this.form);"}}
          {{else}}
            {{mb_label object=$_line field=debut}}
            {{mb_value object=$_line field=debut}}
          {{/if}}

          {{if $can_stop_lines.$category_id}}
            {{mb_label object=$_line field=_datetime_arret}}
            {{mb_field object=$_line field=_datetime_arret form=editLine-$full_line_id register=true onchange="onSubmitFormAjax(this.form);"}}
          {{else}}
            {{mb_label object=$_line field=date_arret}}
            {{mb_value object=$_line field=date_arret}}
          {{/if}}
        </form>
       </td>
    </tr>
    {{else}}
    <tr>
      <th>
        {{if $can_edit_prescription || $app->_ref_user->isKine()}}
          {{mb_include module=ssr template=vw_line_alerte_ssr line=$_line}}
        {{/if}}

        {{if !@$offline && ($can_edit_prescription || $can_stop_lines.$category_id)}}
        <button class="edit notext" type="button" onclick="updateListLines('{{$category_id}}', '{{$_line->prescription_id}}', '{{$_line->_id}}');">
          {{tr}}Edit{{/tr}}
        </button>
          {{if @!$only_comment && $can_edit_prescription}}
          <button class="add notext me-tertiary" onclick="duplicateSSRLine('{{$_line->element_prescription_id}}','{{$category_id}}')">
            {{tr}}Duplicate{{/tr}}
          </button>
          {{/if}}
        {{/if}}
      </th>

      <td class="text" style="vertical-align: middle;">
        {{mb_include module=ssr template=inc_vw_line}}
      </td>
    </tr>
    {{/if}}
  {{/foreach}}
{{/foreach}}
