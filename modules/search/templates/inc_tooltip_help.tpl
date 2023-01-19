{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!--Vue appell�e lors du hover sur l'image d'aide (point d'interrogation) dans la recherche-->

{{mb_default var=display value=true}}
{{if $display}}
  <i class="me-icon help me-primary"
     onmouseover="ObjectTooltip.createDOM(this, 'help-tooltip', {duration: 0})"></i>
  <table class="tbl" id="help-tooltip-old" style="display: none;">
    <tr>
      <th class="title">Recherche avec op�rateurs</th>
    </tr>
    <tr>
        <th class="text">
          Par d�faut la recherche effectu�e est de type "phrase" l'ensemble des mots doivent �tre trouv�s (mot1 AND mot2 AND mot3).<br>
          En activant la recherche approximative les mots sont recherch�s ind�pendamment (mot1 OR mot2 OR mot3) et approximativement.
        </th>
      </tr>
    </table>

  <table class="tbl" id="help-tooltip-old" style="display: none;">
    <tr>
      <th class="title" colspan="2">Recherche avec op�rateurs</th>
    </tr>
    <tr>
      <th class="text" style="width: 300px;">
        {{tr}}mod-search-tooltip-pattern{{/tr}}
      </th>
      <th class="text" style="width: 300px;">
        {{tr}}mod-search-tooltip-pattern-list{{/tr}}
      </th>
    </tr>
    <tr>
      <td class="text" style="width: 300px;">
        {{tr}}mod-search-tooltip-pattern-and{{/tr}}
      </td>
      <td class="text" style="width: 300px;">
        AND
      </td>
    </tr>
    <tr>
      <td class="text" style="width: 300px;">
        OU
      </td>
      <td class="text" style="width: 300px;">
        OR
      </td>
    </tr>
    <tr>
      <td class="text" style="width: 300px;">
        {{tr}}mod-search-tooltip-pattern-not{{/tr}}
      </td>
      <td class="text" style="width: 300px;">
        NOT
      </td>
    </tr>
    <tr>
      <td class="text" style="width: 300px;">
        {{tr}}mod-search-tooltip-pattern-fuzzy{{/tr}}
      </td>
      <td class="text" style="width: 300px;">
        {{tr}}mod-search-tooltip-pattern-word{{/tr}}~
      </td>
    </tr>
    <tr>
      <td class="text" style="width: 300px;">
        {{tr}}mod-search-tooltip-pattern-word{{/tr}} {{tr}}mod-search-tooltip-pattern-word-strict{{/tr}}
      </td>
      <td class="text" style="width: 300px;">
        +{{tr}}mod-search-tooltip-pattern-word{{/tr}}
      </td>
    </tr>
    <tr>
      <td class="text" style="width: 300px;">
        {{tr}}mod-search-tooltip-pattern-word{{/tr}} {{tr}}mod-search-tooltip-pattern-exclude{{/tr}}
      </td>
      <td class="text" style="width: 300px;">
        -{{tr}}mod-search-tooltip-pattern-word{{/tr}}
      </td>
    </tr>
    <tr>
      <td class="text" style="width: 300px;">
        {{tr}}mod-search-tooltip-pattern-wildcard{{/tr}}
      </td>
      <td class="text" style="width: 300px;">
        *Mot ou M*t etc...
      </td>
    </tr>
    <tr>
      <td class="text" style="width: 300px;">
        {{tr}}mod-search-tooltip-pattern-with{{/tr}}
      </td>
      <td class="text" style="width: 300px;">
        ?ot ou M?t
      </td>
    </tr>
    <tr>
      <td class="text" style="width: 300px;">
          {{tr}}mod-search-tooltip-pattern-grouping{{/tr}}
      </td>
      <td class="text" style="width: 300px;">
        (Mot OR Mot) AND Mot
      </td>
    </tr>
  </table>
{{/if}}
