{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=element_selected value=""}}

{{mb_script module=salleOp script=geste_perop ajax=true}}

<ul id="chapitres_geste">
  {{foreach from=$chapters key=key_chapter item=_chapter}}
    <li id="chapitre_{{$_chapter->_id}}" class="chapitre_geste">
      <div class="chapitres-container {{if !$_chapter->_id}}{{$element_selected}}{{/if}}" onclick="GestePerop.showMenuCategories(this, '{{$_chapter->_id}}', null, 1, '{{$see_all_gestes}}');">
        <span class="fold">
          <i class="far fa-caret-square-right fa-lg"></i>
        </span>
        <span title="{{$_chapter->description|smarty:nodefaults}}">
          {{$key_chapter}}
        </span>
      </div>
    </li>
  {{foreachelse}}
    <li class="empty">
      <div class="categories-container" onclick="">
        <span class="empty">
           {{tr}}CAnesthPeropChapitre.none{{/tr}}
        </span>
      </div>
    </li>
  {{/foreach}}
</ul>
