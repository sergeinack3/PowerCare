{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{*1er niveau*}}
{{foreach from=$chapitres item=_chapitre}}
  <option style="padding-left: 0;" value="{{$_chapitre->_id}}" {{if $chapitre_id == $_chapitre->_id}}selected="selected"{{/if}} >
    {{$_chapitre->_view}}
  </option>
  {{*2ème niveau*}}
  {{foreach from=$_chapitre->_ref_chapitres_doc item=_chapitre2}}
    <option style="padding-left: 2em;" value="{{$_chapitre2->_id}}" {{if $chapitre_id == $_chapitre2->_id}}selected="selected"{{/if}} >
      {{$_chapitre2->_view}}
    </option>
    {{*3ème niveau*}}
    {{foreach from=$_chapitre2->_ref_chapitres_doc item=_chapitre3}}
      <option style="padding-left: 4em;" value="{{$_chapitre3->_id}}"
              {{if $chapitre_id == $_chapitre3->_id}}selected="selected"{{/if}} >
        {{$_chapitre3->_view}}
      </option>
      {{*4ème niveau*}}
      {{foreach from=$_chapitre3->_ref_chapitres_doc item=_chapitre4}}
        <option style="padding-left: 6em;" value="{{$_chapitre4->_id}}"
                {{if $chapitre_id == $_chapitre4->_id}}selected="selected"{{/if}} >
          {{$_chapitre4->_view}}
        </option>
        {{*5ème niveau*}}
        {{foreach from=$_chapitre4->_ref_chapitres_doc item=_chapitre5}}
          <option style="padding-left: 8em;" value="{{$_chapitre5->_id}}"
                  {{if $chapitre_id == $_chapitre5->_id}}selected="selected"{{/if}} >
            {{$_chapitre5->_view}}
          </option>
        {{/foreach}}
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
{{/foreach}}
