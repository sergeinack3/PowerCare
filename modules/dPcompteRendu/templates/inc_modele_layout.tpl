{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_factory value=1}}

{{if !$compte_rendu->_id}}
  <div id="layout" style="display: none; " class="small-info">
    {{tr}}CCompteRendu-msg-No layout possible{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="form me-no-border" id="layout" style="display: none;">
  <tr class="notice">
    <td>
      <div class="small-info">
        {{tr}}CCompteRendu-msg-This model is not a body of text{{/tr}}
      </div>
    </td>
  </tr>

  <tbody class="fields">
  {{if $pdf_and_thumbs}}
    <tr>
      <th class="category" colspan="2">
        {{tr}}CCompteRendu-Pagelayout{{/tr}}
      </th>
    </tr>
    <tr id="page_layout" style="display: none;">
      <td colspan="2">
        {{mb_include template=inc_page_layout}}
      </td>
    </tr>
  {{/if}}

  <tr id="height" style="display: none;">
    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field=height}}
      {{if $droit}}
        {{mb_field object=$compte_rendu field=height increment=true form=editFrm onchange="Thumb.old(); Modele.preview_layout();" step="10" onkeyup="Modele.preview_layout();"}}
        <button id="button_addedit_modeles_generate_auto_height" type="button" class="change" onclick="Thumb.old(); Modele.generate_auto_height(); Modele.preview_layout();">{{tr}}CCompteRendu.auto_height{{/tr}}</button>
      {{else}}
        {{mb_field object=$compte_rendu field=height readonly="readonly"}}
      {{/if}}
    {{/me_form_field}}
  </tr>

  <tr id="layout_header_footer" style="display: none;">
    <th>{{tr}}CCompteRendu-preview-header-footer{{/tr}}</th>
    <td>
      <div id="preview_page" style="color: #000; height: 84px; padding: 7px; width: 58px; background: #fff; border: 1px solid #000; overflow: hidden;">
        <div id="header_footer_content" style="color: #000; white-space: normal; background: #fff; overflow: hidden; margin: -1px; height: 30px; width: 100%; font-size: 3px;">
          {{mb_include template=lorem_ipsum}}
        </div>
        <hr style="width: 100%; margin-top: 3px; margin-bottom: 3px;"/>
        <div id="body_content" style="margin: -1px; color: #999; height: 50px; width: 100%; font-size: 3px; white-space: normal; overflow: hidden;">
          {{mb_include template=lorem_ipsum}}
        </div>
      </div>
    </td>
  </tr>
  </tbody>
</table>
