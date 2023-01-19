{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
    {{foreach from=$matches key=_key item=_match}}
      <li data-class="{{$_match->getShortname()}}" data-table="{{$_match->getTableName()}}"
          data-module="{{$_match->getModule()}}" class="classAutocomplete-container">
          {{if $profile === 'full'}}
            {{mb_include module=system template=autocomplete/inc_vw_classes_autocomplete_full
              class_tr=$_match->getClassTranslation()
              shortname=$_match->getShortname()
              module_tr=$_match->getModuleTranslation()
              table_name=$_match->getTableName()
            }}
          {{else}}
            <div class="classAutocompleteSmall">
                {{if $profile === 'className'}}
                  <span
                    class="classAutocompleteSmall-smallTitle">{{$_match->getClassTranslation()|emphasize:$keywords:'mark'}}
                  </span>
                  - {{$_match->getShortname()|emphasize:$keywords:'mark'}}
                {{elseif $profile === 'moduleName'}}
                  <span
                    class="classAutocompleteSmall-smallTitle">{{$_match->getModuleTranslation()|emphasize:$keywords:'mark'}}
                  </span>
                {{elseif $profile === 'tableName'}}
                  <span
                    class="classAutocompleteSmall-smallTitle">{{$_match->getTableName()|emphasize:$keywords:'mark'}}
                  </span>
                {{/if}}
            </div>
          {{/if}}
      </li>
    {{/foreach}}
</ul>






