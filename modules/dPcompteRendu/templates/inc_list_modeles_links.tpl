{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-align-auto me-no-border-radius-top">
  <tr>
    <th>{{tr}}common-Name{{/tr}}</th>
    <th>{{tr}}common-Context{{/tr}}</th>
    <th id="selected_doc" {{if !$pack->is_eligible_selection_document}} style="display: none" {{/if}}>{{tr}}CModeleToPack-is_selected{{/tr}}</th>
    <th></th>
  </tr>
  {{foreach from=$pack->_back.modele_links item=_link}}
  <tr>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_link->_ref_modele->_guid}}')">
        {{$_link}}
      </span>
    </td>
    <td>
      {{tr}}{{$_link->_ref_modele->object_class}}{{/tr}}
      {{if $pack->object_class != $_link->_ref_modele->object_class}}
        <i class="fas fa-exclamation-triangle" style="color: #ff9502; font-size: 14px" title="{{tr}}CCompteRendu-Bad context{{/tr}}"></i>
      {{/if}}
    </td>
    <td class="selected_doc" {{if !$pack->is_eligible_selection_document}} style="display: none" {{/if}}>
      {{foreach from=$modeles_to_pack item=modele_to_pack}}
        {{if $modele_to_pack->modele_id == $_link->_ref_modele->_id}}
          <form name="select_document_{{$modele_to_pack->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
            {{mb_key object=$modele_to_pack}}
            {{mb_class object=$modele_to_pack}}
            {{mb_field object=$modele_to_pack field=is_selected typeEnum=checkbox onChange="this.form.onsubmit()"}}
          </form>
        {{/if}}
      {{/foreach}}
    </td>
    <td class="narrow">
      <form name="Del-{{$_link->_guid}}" action="?" method="post" onsubmit="return Pack.onSubmitModele(this);">
        {{mb_class object=$_link}}
        {{mb_key   object=$_link}}
        <input type="hidden" name="del" value="1" />
        <button class="remove notext compact me-tertiary" type="submit">{{tr}}Delete{{/tr}}</button>
      </form>
    </td>
  </tr>

  {{foreachelse}}
  <tr>
    <td class="empty">{{tr}}CPack-back-modele_links.empty{{/tr}}</td>
  </tr>

  {{/foreach}}
</table>
