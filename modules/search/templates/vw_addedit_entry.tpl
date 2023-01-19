{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!--Formulaire d'ajout/édition d'un favoris-->

<script>
  targetEditCallback = function () {
    Thesaurus.url_addeditThesaurusEntry.refreshModal();
  };
  var form = getForm("addeditFavoris");
  var cont_type = $('cont_types'),
    element_type = form.types;

  window.types = new TokenField(element_type, {
    onChange: function () {
    }.bind(element_type)
  });

  setValueUser = function () {
    var form = getForm("addeditFavoris");
    $V(form.elements.user_id, '{{$user_thesaurus->_id}}');
    $V(form.elements.function_id, '');
    $V(form.elements.group_id, '');
  };

  setValueFunction = function () {
    var form = getForm("addeditFavoris");
    $V(form.elements.user_id, '{{$user_thesaurus->_id}}');
    $V(form.elements.function_id, '{{$user_thesaurus->_ref_function->_id}}');
    $V(form.elements.group_id, '');
  };

  setValueGroup = function () {
    var form = getForm("addeditFavoris");
    $V(form.elements.user_id, '{{$user_thesaurus->_id}}');
    $V(form.elements.group_id, '{{$user_thesaurus->_ref_function->_ref_group->_id}}');
    $V(form.elements.function_id, '{{$user_thesaurus->_ref_function->_id}}');
  }
</script>


<form method="post" name="addeditFavoris" onsubmit="return Thesaurus.submitThesaurusEntry(this);">
    {{mb_key   object=$thesaurus_entry}}
    {{mb_class object=$thesaurus_entry}}
  <input type="hidden" name="user_id" value="{{$thesaurus_entry->user_id}}" />
  <input type="hidden" name="function_id" value="{{$thesaurus_entry->function_id}}" />
  <input type="hidden" name="group_id" value="{{$thesaurus_entry->group_id}}" />
  <input type="hidden" name="types" value="{{"|"|implode:$search_types}}" />
  <input type="hidden" name="del" value="0" />
    {{if !$thesaurus_entry->_id}}
      <input type="hidden" name="callback" value="Thesaurus.addeditThesaurusCallback" />
    {{/if}}
  <table class="main form">
    <!-- hide target -->
      {{if $thesaurus_entry->_id}}
        <tr>
          <td>
            <button type="button" class="new"
                    onclick="Thesaurus.addeditTargetEntry('{{$thesaurus_entry->_id}}', window.targetEditCallback)">{{tr}}CSearchCibleEntry-action-add edit{{/tr}}</button>
          </td>
          <td>
            <table>
              <tr>
                <td>
                  <fieldset>
                    <legend>Codes CIM10 :</legend>
                    <ul class="tags">
                        {{foreach from=$thesaurus_entry->_cim_targets item=_target}}
                          <li class="tag me-tag" title="{{$_target->_ref_target->libelle}}"
                              style="background-color: #CCFFCC; cursor:auto">
                              {{$_target->_ref_target->code}}
                          </li>
                            {{foreachelse}}
                          <li><span class="empty">{{tr}}CSearchCibleEntry.none{{/tr}}</span></li>
                        {{/foreach}}
                    </ul>
                  </fieldset>
                </td>
                <td>
                  <fieldset>
                    <legend>Codes CCAM :</legend>
                    <ul class="tags">
                        {{foreach from=$thesaurus_entry->_ccam_targets item=_target}}
                          <li class="tag me-tag" title="{{$_target->_ref_target->libelle_long}}"
                              style="background-color: rgba(153, 204, 255, 0.6); cursor:auto">
                              {{$_target->_ref_target->code}}
                          </li>
                            {{foreachelse}}
                          <li><span class="empty">{{tr}}CSearchCibleEntry.none{{/tr}}</span></li>
                        {{/foreach}}
                    </ul>
                  </fieldset>
                </td>
                <td>
                  <fieldset>
                    <legend>Classes ATC :</legend>
                    <ul class="tags">
                        {{foreach from=$thesaurus_entry->_atc_targets item=_target}}
                          <li class="tag me-tag" title="{{$_target->_libelle}}"
                              style="background-color: rgba(240, 255, 163, 0.6); cursor:auto">
                              {{$_target->object_id}}
                          </li>
                            {{foreachelse}}
                          <li><span class="empty">{{tr}}CSearchCibleEntry.none{{/tr}}</span></li>
                        {{/foreach}}
                    </ul>
                  </fieldset>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      {{/if}}
    <tr>
      <td colspan="2">
        <span class="circled">
          <img src="images/icons/user.png" title="Favoris pour {{$user_thesaurus->_view}}">
          <label for="user">Utilisateur</label>
          <input type="radio" id="user" name="_choose" value="{{$user_thesaurus->_id}}" onclick="setValueUser()"
                 {{if $thesaurus_entry->user_id}}checked{{/if}}>
        </span>

        <span class="circled">
           <img src="images/icons/user-function.png" title="Favoris pour {{$user_thesaurus->_ref_function}}">
          <label for="function"> Fonction</label>
          <input id="function" type="radio" name="_choose" onclick="setValueFunction()"
                 {{if $thesaurus_entry->function_id}}checked{{/if}}>
        </span>

        <span class="circled">
          <img src="images/icons/group.png" title="Favoris pour {{$user_thesaurus->_ref_function->_ref_group}}">
          <label for="group"> Etablissement</label>
          <input id="group" type="radio" name="_choose" onclick="setValueGroup()"
                 {{if $thesaurus_entry->group_id}}checked{{/if}}>
        </span>
      </td>
    </tr>
    <tr>
      <td>
          {{mb_label object=$thesaurus_entry field=titre}}
      </td>
      <td>
          {{mb_field object=$thesaurus_entry field=titre}}
      </td>
    </tr>
    <tr>
      <td class="text narrow">
          {{tr}}CSearchThesaurusEntry-Pattern{{/tr}}
      </td>
      <td>
          {{mb_include module=search template=inc_tooltip_help}}
        <div style="display:none">
          <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title and{{/tr}}"
                  onclick="Thesaurus.addPatternToEntry('add')">{{tr}}CSearchThesaurusEntry-Pattern and{{/tr}}</button>
          <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title or{{/tr}}"
                  onclick="Thesaurus.addPatternToEntry('or')">{{tr}}CSearchThesaurusEntry-Pattern or{{/tr}}</button>
          <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title not{{/tr}}"
                  onclick="Thesaurus.addPatternToEntry('not')">{{tr}}CSearchThesaurusEntry-Pattern not{{/tr}}</button>
          <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title like{{/tr}}"
                  onclick="Thesaurus.addPatternToEntry('like')">{{tr}}CSearchThesaurusEntry-Pattern like{{/tr}}</button>
          <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title obligation{{/tr}}"
                  onclick="Thesaurus.addPatternToEntry('obligation')">{{tr}}CSearchThesaurusEntry-Pattern obligation{{/tr}}</button>
          <button type="button" title="{{tr}}CSearchThesaurusEntry-Pattern-title prohibition{{/tr}}"
                  onclick="Thesaurus.addPatternToEntry('prohibition')">{{tr}}CSearchThesaurusEntry-Pattern prohibition{{/tr}}</button>
        </div>
      </td>
    </tr>
    <tr>
      <td>
          {{mb_label object=$thesaurus_entry field=entry}}
      </td>
      <td>
          {{mb_field object=$thesaurus_entry field=entry}}
      </td>
    </tr>
    <tr>
      <td>
          {{mb_label object=$thesaurus_entry field=types}}
      </td>
      <td id="cont_types" class="columns-2">
          {{foreach from=$types item=_type}}
            <label> <input type="checkbox" name="addeditFavoris_{{$_type}}" id="{{$_type}}" value="{{$_type}}"
                           {{if in_array($_type, $search_types)}}checked{{/if}}
                           onclick="window.types.toggle(this.value, this.checked);">
                {{tr}}{{$_type}}{{/tr}}</label>
            <br />
          {{/foreach}}
      </td>
    </tr>
    <tr>
      <td>
          {{mb_label object=$thesaurus_entry field=contextes}}
      </td>
      <td>
          {{mb_field object=$thesaurus_entry field=contextes}}
      </td>
    </tr>
    <tr>
      <td>
          {{mb_label object=$thesaurus_entry field=agregation}}
      </td>
      <td>
          {{mb_field object=$thesaurus_entry field=agregation}}
      </td>
    </tr>
    <tr>
      <td>
          {{mb_label object=$thesaurus_entry field=fuzzy}}
      </td>
      <td>
          {{mb_field object=$thesaurus_entry field=fuzzy}}
      </td>
    </tr>
    <tr>
      <td>
          {{mb_label object=$thesaurus_entry field=search_auto}}
      </td>
      <td>
          {{mb_field object=$thesaurus_entry field=search_auto}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
          {{if $thesaurus_entry->_id}}
            <button type="submit" class="trash" onclick="$V(this.form.del,'1')">{{tr}}Delete{{/tr}}</button>
          {{/if}}
      </td>
    </tr>
  </table>
</form>
