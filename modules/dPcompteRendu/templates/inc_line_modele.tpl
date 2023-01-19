{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_tr value=true}}

<script>
  Main.add(function() {
    {{if !$_modele->actif}}
      $("line_modele_{{$_modele->_id}}").addClassName("opacity-50");
    {{else}}
      $("line_modele_{{$_modele->_id}}").removeClassName("opacity-50");
    {{/if}}
  });
</script>

{{assign var=readonly value=0}}

{{if $_modele->_is_for_instance && !$app->_ref_user->isAdmin()}}
  {{assign var=readonly value=1}}
{{/if}}

{{if $with_tr}}
<tr id="line_modele_{{$_modele->_id}}" class="{{if $_modele->_id == $filtre->_id}}selected{{/if}} line">
{{/if}}

  <td>
    {{if !$readonly}}
      <input type="checkbox" class="export_modele" value="{{$_modele->_id}}" />
    {{/if}}
  </td>
  <td colspan="2" class="text">
    {{if $_modele->fast_edit_pdf}}
      <img style="float: right;" src="modules/dPcompteRendu/fcke_plugins/mbprintPDF/images/mbprintPDF.png" />
    {{elseif $_modele->fast_edit}}
      <img style="float: right;" src="modules/dPcompteRendu/fcke_plugins/mbprinting/images/mbprinting.png" />
    {{/if}}
    {{if $_modele->fast_edit || $_modele->fast_edit_pdf}}
      <img style="float: right;" src="images/icons/lightning.png" />
    {{/if}}

    {{if !$readonly}}
    <a href="#1" onclick="updateSelected(this.up('tr')); Modele.edit('{{$_modele->_id}}')">
    {{/if}}
      {{assign var=object_class value=$_modele->object_class}}
      {{assign var=name         value=$_modele->nom}}
      <span class="CCompteRendu-view">
        {{if $_modele->_special_modele}}
          <strong>Special:</strong>
          {{tr}}CCompteRendu.description_{{$_modele->nom}}{{/tr}}
        {{else}}
          {{mb_value object=$_modele field=nom}}
        {{/if}}
      </span>
    {{if !$readonly}}
    </a>
    {{/if}}
  </td>

  <td>{{mb_value object=$_modele field=object_class}}</td>

  <td>{{$_modele->_ref_category}}</td>

  <td>
    {{mb_value object=$_modele field=type}}
    <div class="compact">
      {{if $_modele->type == "body"}}
        {{assign var=header value=$_modele->_ref_header}}
        {{if $header->_id}}
          +
          <span onmouseover="ObjectTooltip.createEx(this, '{{$header->_guid}}');">
                {{$header->nom}}
              </span>
        {{/if}}

        {{assign var=preface value=$_modele->_ref_preface}}
        {{if $preface->_id}}
          +
          <span onmouseover="ObjectTooltip.createEx(this, '{{$preface->_guid}}');">
                {{$preface->nom}}
              </span>
        {{/if}}

        {{assign var=ending value=$_modele->_ref_ending}}
        {{if $ending->_id}}
          +
          <span onmouseover="ObjectTooltip.createEx(this, '{{$ending->_guid}}');">
                {{$ending->nom}}
              </span>
        {{/if}}

        {{assign var=footer value=$_modele->_ref_footer}}
        {{if $footer->_id}}
          +
          <span onmouseover="ObjectTooltip.createEx(this, '{{$footer->_guid}}');">
                {{$footer->nom}}
              </span>
        {{/if}}
      {{elseif $_modele->type == "header"}}
        {{assign var=count value=$_modele->_count.modeles_headed}}
        {{if $count}}
          {{$_modele->_count.modeles_headed}}
          {{tr}}CCompteRendu-back-modeles_headed{{/tr}}
        {{else}}
          <div class="empty">{{tr}}CCompteRendu-back-modeles_headed.empty{{/tr}}</div>
        {{/if}}
      {{elseif $_modele->type == "preface"}}
        {{assign var=count value=$_modele->_count.modeles_prefaced}}
        {{if $count}}
          {{$_modele->_count.modeles_prefaced}}
          {{tr}}CCompteRendu-back-modeles_prefaced{{/tr}}
        {{else}}
          <div class="empty">{{tr}}CCompteRendu-back-modeles_prefaced.empty{{/tr}}</div>
        {{/if}}
      {{elseif $_modele->type == "ending"}}
        {{assign var=count value=$_modele->_count.modeles_ended}}
        {{if $count}}
          {{$_modele->_count.modeles_ended}}
          {{tr}}CCompteRendu-back-modeles_ended{{/tr}}
        {{else}}
          <div class="empty">{{tr}}CCompteRendu-back-modeles_ended.empty{{/tr}}</div>
        {{/if}}
      {{elseif $_modele->type == "footer"}}
        {{assign var=count value=$_modele->_count.modeles_footed}}
        {{if $count}}
          {{$_modele->_count.modeles_footed}}
          {{tr}}CCompteRendu-back-modeles_footed{{/tr}}
        {{else}}
          <div class="empty">{{tr}}CCompteRendu-back-modeles_footed.empty{{/tr}}</div>
        {{/if}}
      {{/if}}
    </div>
  </td>

  {{if "dmp"|module_active}}
    <td>
      {{if $_modele->type_doc_dmp}}
        {{'Ox\Mediboard\Files\CDocumentItem::getDisplayNameDmp'|static_call:$_modele->type_doc_dmp}}
      {{else}}
        &dash;
      {{/if}}
    </td>
  {{/if}}

  <td style="text-align: center;">
    {{if $_modele->_ref_content->_image_status}}
      <i class="fas fa-check" style="color: #080; font-size: 1.1em;" />
    {{else}}
      <i class="fas fa-times" style="color: #800; font-size: 1.1em; cursor: help;"
         onmouseover="ObjectTooltip.createDOM(this, 'image_status_{{$_modele->_id}}');" />

      <div id="image_status_{{$_modele->_id}}" style="display: none;">
        <table class="tbl">
          <tr>
            <th class="title">{{tr}}CContentHTML-List of images issues{{/tr}}</th>
          </tr>
          {{foreach from=$_modele->_ref_content->_images item=_count key=_issue}}
            <tr>
              <td>
                {{tr var1=$_count}}CContentHTML-Issue image {{$_issue}}{{/tr}}
              </td>
            </tr>
          {{/foreach}}
        </table>
      </div>
    {{/if}}
  </td>

  <td>
    {{mb_value object=$_modele field=_date_last_use}}
  </td>

  <td style="text-align: center;">
    {{if $_modele->type == "body"}}
      <strong>{{$_modele->_count.documents_generated|nozero}}</strong>
    {{/if}}
  </td>
  <td>
    <button class="notext stats" onclick="Modele.showUtilisation('{{$_modele->_id}}');">{{tr}}Stats{{/tr}}</button>
  </td>

  <td>
    {{if !$readonly}}
      {{assign var=modele_nom value=$_modele->nom|smarty:nodefaults|JSAttribute}}

      {{me_button label=Export icon=hslip old_class=notext onclick="Modele.exportXML('$modele_nom', '', [`$_modele->_id`])"}}

      {{if $_modele->_canEdit}}
        {{me_button label=Delete icon=trash old_class=notext onclick="Modele.remove('`$_modele->_id`', '$modele_nom')"}}
      {{/if}}

      {{me_dropdown_button button_label=Actions button_icon="opt"
                            button_class="notext me-tertiary" container_class="me-dropdown-button-right"}}
    {{/if}}
  </td>

{{if $with_tr}}
</tr>
{{/if}}