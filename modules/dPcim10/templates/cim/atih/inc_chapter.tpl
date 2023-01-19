{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<li id="chapter-{{$chapter->code}}">
  <div class="chapter-container">
  {{if $chapter->_categories|@count}}
    <span class="fold" onclick="CIM.foldChapter('{{$chapter->code}}', this);">
      <i class="far fa-caret-square-right fa-lg"></i>
    </span>
  {{/if}}
    <span onclick="CIM.showCode('{{$chapter->code}}', 'chapter');">
      <span class="cim10-code">
        {{$chapter->code}}
      </span>
      &mdash; {{$chapter->libelle}}
    </span>
  </div>
  {{if $chapter->_categories|@count}}
    <ul class="cim10_subcategories" id="categories-{{$chapter->code}}" style="display: none;">
      {{foreach from=$chapter->_categories item=category}}
        <li id="chapter-{{$category->code}}" class="categories-{{$chapter->code}}" onclick="CIM.showCode('{{$category->code}}', 'chapter');">
          <span class="cim10-code">
            {{$category->code}}
          </span>
          &mdash; {{$category->libelle}}
        </li>
      {{/foreach}}
    </ul>
  {{/if}}
</li>