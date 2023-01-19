{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="title" colspan="2">
      <input type="hidden" name="suivi[etat]" value="{{$docGed|const:'VALID'}}" />
      {{tr}}_CDocGed_TERMINE{{/tr}}
    </th>
  </tr>
  <tr>
    <th>{{tr}}CDocGedSuivi-doc_ged_suivi_id-court{{/tr}}</th>
    <td>
      {{if $docGed->_lastactif->doc_ged_suivi_id}}
        {{$docGed->_reference_doc}}
        <br />
        {{tr}}CDocGed-version-court{{/tr}} : {{$docGed->version}}
      {{/if}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}CDocGed-doc_theme_id{{/tr}}</th>
    <td class="text">
      {{$docGed->_ref_theme->nom}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}CDocGed-group_id{{/tr}}</th>
    <td class="text">
      {{$docGed->_ref_group->text}}
    </td>
  </tr>
  <tr>
    <td colspan="2" class="button">
      <a href="#" onclick="popFile('{{$docGed->_class}}','{{$docGed->_id}}','CFile','{{$docGed->_lastactif->file_id}}')"
         title="{{tr}}CFile-msg-viewfile{{/tr}}">
        {{thumbnail file_id=$docGed->_lastactif->file_id profile=small alt="-" style="max-width:64px; max-height:64px;"}}
      </a>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="button">
      {{if $docGed->annule}}
        <button class="change" type="button" onclick="annuleDoc(this.form,0);">
          {{tr}}button-CDocGed-retablir{{/tr}}
        </button>
      {{else}}
        <button class="cancel" type="button" onclick="annuleDoc(this.form,1);">
          {{tr}}Cancel{{/tr}}
        </button>
      {{/if}}
    </td>
  </tr>
</table>