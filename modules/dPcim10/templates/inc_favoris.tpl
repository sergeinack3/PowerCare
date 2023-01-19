{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10 script=CIM}}

{{if !$reload}}
  <div id="favoris_user_view">
{{/if}}

<table class="main tbl me-no-box-shadow">
  <tr>
    <td colspan="4">
      <form name="selectLang" method="get">
        <input type="hidden" name="m" value="cim10" />
        <input type="hidden" name="tab" value="vw_idx_favoris" />

        <label for="tag_id">Tag</label>
        <select name="tag_id" onchange="this.form.submit()" class="taglist">
          <option value=""> &mdash; {{tr}}All{{/tr}} </option>
          {{mb_include module=ccam template=inc_favoris_tag_select depth=0 show_empty=true}}
        </select>

        {{if $can->admin}}
          <button style="float: right;" class="tag-edit" type="button" onclick="Tag.manage('CFavoriCIM10')">
            {{tr}}CFavoriCIM10-edit_tags{{/tr}}
          </button>
        {{/if}}
      </form>
    </td>
  </tr>

  {{foreach from=$fusionCim item=curr_code key=curr_key name="fusion"}}
  <tr>
    <td class="narrow">
      {{if $can->edit && $curr_code->_favoris_id}}
        <form name="delFavoris-{{$curr_key}}" method="post"
              onsubmit="return onSubmitFormAjax(this, function() { document.location.reload(); });">
          {{mb_class class=CFavoriCIM10}}
          <input type="hidden" name="del" value="1" />
          <input type="hidden" name="favoris_id" value="{{$curr_code->_favoris_id}}" />
          <button class="trash notext compact me-tertiary" type="submit">{{tr}}CFavoriCIM10-action-delete{{/tr}}</button>
        </form>
      {{/if}}
    </td>

    <td style="font-weight: bold;">
      <a href="#1" onclick="CIM.showCodeModal('{{$curr_code->code}}');">{{$curr_code->code}}</a>
    </td>
    <td class="text">
      {{if $curr_code->_favoris_id && $can->edit}}
        <form name="favoris-tag-{{$curr_code->_favoris_id}}" method="post" style="float: right;">
          {{if $curr_code->_favoris_id}}
            {{mb_include module=system
              template=inc_tag_binder_widget
              object=$curr_code->_ref_favori
              show_button=false
              form_name="favoris-tag-`$curr_code->_favoris_id`"
              callback="CIM.reloadFavoris"
            }}
          {{/if}}
        </form>
      {{/if}}

      <a href="#1" onclick="CIM.showCodeModal('{{$curr_code->code}}');">{{$curr_code->libelle}}</a>
    </td>
    <td>{{if $curr_code->occurrences==0}}{{tr}}CFavoriCIM10|pl{{/tr}}{{else}}{{$curr_code->occurrences}} {{tr}}CFavoriCIM10-used{{/tr}}{{/if}}</td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="4">{{tr}}CFavoriCIM10.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>

{{if !$reload}}
  </div>
{{/if}}