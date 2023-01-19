{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<table class="tbl">
  <tr>
    <th colspan="4" class="title">{{$lieu->label}} - {{tr}}CAgendaPraticien-list{{/tr}}</th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th class="narrow">{{mb_label object=$assoc field="active"}}</th>
    <th class="narrow">{{mb_label object=$assoc field="sync"}}</th>
    <th>{{mb_label object=$assoc field="praticien_id"}}</th>
  </tr>
  {{foreach from=$assocList item=_assoc}}
    <tr>
      <td>
        <form name="erase-agenda{{$_assoc->_id}}" method="post" onsubmit="return onSubmitFormAjax(this,Control.Modal.refresh.curry());">
          {{mb_key object=$_assoc}}
          {{mb_class object=$_assoc}}
          <input type="hidden" name="del" value="1" />
          <button type="submit" class="trash notext me-secondary">{{tr}}CAgendaPraticien-action-delete{{/tr}}</button>
        </form>
      </td>
      <td>
        <form name="active-agenda" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_key object=$_assoc}}
          {{mb_class object=$_assoc}}
          {{mb_field object=$_assoc field=active typeEnum=checkbox onchange="this.form.onsubmit();"}}
        </form>
      </td>
      <td>
        <form name="active-agenda" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_key object=$_assoc}}
          {{mb_class object=$_assoc}}
          {{mb_field object=$_assoc field=sync typeEnum=checkbox onchange="this.form.onsubmit();"}}
        </form>
      </td>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_assoc->_ref_praticien}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="2">{{tr}}CAgendaPraticien.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

{{if $can->admin}}
  <form name="assoc-lieu" action="?" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.refresh.curry())">
    {{mb_key object=$assoc}}
    {{mb_class object=$assoc}}
    {{mb_field object=$assoc field="lieuconsult_id" value=$lieu->_id hidden=true}}
    <table class="form">
      {{mb_include module=system template=inc_form_table_header object=$assoc}}
      <tr>
        {{me_form_field nb_cells=2 mb_object=$assoc mb_field="praticien_id"}}
          <select name="praticien_id" style="width: 15em;" onchange="return onSubmitFormAjax(getForm('assoc-lieu'), Control.Modal.refresh.curry())">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_mediuser list=$listPraticien}}
          </select>
        {{/me_form_field}}
      </tr>
      <tr>
        <td class="button" colspan="2">
          <button type="button" class="close me-primary" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
{{/if}}