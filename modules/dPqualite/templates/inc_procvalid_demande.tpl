{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="info_proc" value=$docGed->_lastentry->date|date_format:"%d %b %Y à %Hh%M"}}
<table class="form">
  <tr>
    <th class="title modify" colspan="2">
      <input type="hidden" name="suivi[etat]" value="{{$docGed|const:'VALID'}}" />
      {{tr}}_CDocGed_DEMANDE{{/tr}}
    </th>
  </tr>
  <tr>
    <th>{{tr}}Date{{/tr}}</th>
    {{assign var=lastentry value=$docGed->_lastentry}}
    <td>{{mb_field object=$lastentry field=date form="ProcEditFrm" register="true"}}</td>
  </tr>
  <tr>
    <th>{{tr}}CDocGedSuivi-doc_ged_suivi_id-court{{/tr}}</th>
    <td>
      {{if $docGed->doc_ged_id && $docGed->_lastactif->doc_ged_suivi_id}}
        {{tr}}_CDocGed_revision{{/tr}} {{$docGed->_reference_doc}}
        <br />
        {{tr}}CDocGed-doc_theme_id{{/tr}} : {{$docGed->_ref_theme->nom}}
      {{else}}
        {{tr}}_CDocGed_new{{/tr}}
      {{/if}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}CDocGed-group_id{{/tr}}</th>
    <td class="text">
      {{$docGed->_ref_group->text}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}CDocGedSuivi-user_id-court{{/tr}}</th>
    <td class="text">{{$docGed->_lastentry->_ref_user->_view}}</td>
  </tr>
  <tr>
    <th>{{tr}}CDocGedSuivi-remarques{{/tr}}</th>
    <td class="text">
      {{$docGed->_lastentry->remarques|nl2br}}
    </td>
  </tr>
  <tr>
    <td colspan="2" class="button">
      <button class="cancel" type="button" onclick="refuseDoc(this.form);">
        {{tr}}button-CDocGed-refusdemande{{/tr}}
      </button>
    </td>
  </tr>
  <tr>
    <th>
      <label for="ged[titre]" title="{{tr}}CDocGed-titre-desc{{/tr}}">
        {{tr}}CDocGed-titre{{/tr}}
      </label>
    </th>
    <td>
      <input type="text" name="ged[titre]" value="{{$docGed->titre}}" class="notNull {{$docGed->_props.titre}}" />
    </td>
  </tr>
  <tr>
    <th>
      <label for="ged[doc_theme_id]" title="{{tr}}CDocGed-doc_theme_id-desc{{/tr}}">
        {{tr}}CDocGed-doc_theme_id{{/tr}}
      </label>
    </th>
    <td>
      <select name="ged[doc_theme_id]" class="{{$docGed->_props.doc_theme_id}}">
        <option value="">&mdash; {{tr}}CDocGed-doc_theme_id-desc{{/tr}}</option>
        {{foreach from=$listThemes item=curr_theme}}
          <option
            value="{{$curr_theme->doc_theme_id}}" {{if $docGed->doc_theme_id == $curr_theme->doc_theme_id}} selected="selected" {{/if}} >
            {{$curr_theme->nom}}
          </option>
        {{/foreach}}
      </select>
    </td>
  </tr>
  {{if !$docGed->_lastactif->doc_ged_suivi_id}}
    <tr>
      <th>
        <label for="ged[doc_chapitre_id]" title="{{tr}}CDocGed-doc_chapitre_id-desc{{/tr}}">
          {{tr}}CDocGed-doc_chapitre_id{{/tr}}
        </label>
      </th>
      <td>
        <select name="ged[doc_chapitre_id]" class="notNull {{$docGed->_props.doc_chapitre_id}}">
          <option value="">&mdash; {{tr}}CDocGed-doc_chapitre_id-desc{{/tr}}</option>
          {{mb_include module=qualite template=inc_options_chapitres chapitres=$listChapitres chapitre_id=$docGed->doc_chapitre_id}}
        </select>
      </td>
    </tr>
    <tr>
      <th>
        <label for="ged[doc_categorie_id]" title="{{tr}}CDocGed-doc_categorie_id-desc{{/tr}}">
          {{tr}}CDocGed-doc_categorie_id{{/tr}}
        </label>
      </th>
      <td>
        <select name="ged[doc_categorie_id]" class="notNull {{$docGed->_props.doc_categorie_id}}">
          <option value="">&mdash; {{tr}}CDocGed-doc_categorie_id-desc{{/tr}}</option>
          {{foreach from=$listCategories item=curr_category}}
            <option
              value="{{$curr_category->doc_categorie_id}}" {{if $docGed->doc_categorie_id == $curr_category->doc_categorie_id}} selected="selected" {{/if}} >
              {{$curr_category->_view}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  {{/if}}
  <tr>
    <th>
      <label for="ged[num_ref]" title="{{tr}}CDocGed-num_ref-desc{{/tr}}">
        {{tr}}CDocGed-num_ref{{/tr}}
      </label>
    </th>
    <td>
      <input type="text" name="ged[num_ref]" class="{{$docGed->_props.num_ref}}" value="{{$docGed->num_ref}}" />
    </td>
  </tr>
  <tr>
    <th><label for="formfile[0]">{{tr}}CFile{{/tr}}</label></th>
    <td>
      <input type="hidden" name="object_class" value="CDocGed" />
      <input type="hidden" name="object_id" value="{{$docGed->doc_ged_id}}" />
      <input type="hidden" name="file_category_id" value="" />
      <input type="file" name="formfile[0]" size="0" class="str" />
    </td>
  </tr>
  <tr>
    <th><label for="suivi[remarques]" title="{{tr}}CDocGed-remarques-desc{{/tr}}">{{tr}}CDocGed-remarques{{/tr}}</label></th>
    <td>
      <textarea name="suivi[remarques]" class="{{$docGed->_lastentry->_props.remarques}}"></textarea>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="button">
      <button class="tick" type="button" onclick="redactionDoc(this.form);">
        {{tr}}button-CDocGed-accept{{/tr}}
      </button>
      <button class="trash" type="button"
              onclick="confirmDeletion(this.form, {typeName:'{{tr escape="javascript"}}CDocGed.one{{/tr}}',objName:'{{$info_proc|smarty:nodefaults|JSAttribute}}'})">
        {{tr}}Delete{{/tr}}
      </button>
      <br />
      <button class="tick" type="button" onclick="validDocDirect(this.form);">
        {{tr}}button-CDocGed-valid{{/tr}}
      </button>
    </td>
  </tr>
</table>