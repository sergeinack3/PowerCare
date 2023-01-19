{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>{{tr}}CCIM10-title-summary{{/tr}}</legend>
  <ul id="summary_cim" class="me-no-border-left me-no-border-right">
    {{foreach from=$chapters item=chapter}}
      {{mb_include module=cim10 template="cim/$version/inc_chapter"}}
    {{foreachelse}}
      <li class="empty">{{tr}}CCodeCIM10-chapter.none{{/tr}}</li>
    {{/foreach}}
  </ul>
</fieldset>