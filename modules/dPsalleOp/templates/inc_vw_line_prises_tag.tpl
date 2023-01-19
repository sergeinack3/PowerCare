{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=view_type value="graph"}}

<span class="compact me-margin-top-1">
  <span class="texticon texticon-hopital me-font-weight-bold me-no-border" style="font-size: 1em;"
        onmouseover="ObjectTooltip.createDOM(this, 'show_planifications_detail_{{$view_type}}_{{$line->_id}}');">
    {{tr var1=$line->_ref_prises|@count}}CPlanificationSysteme-%s planification{{/tr}}
  </span>
</span>

<div id="show_planifications_detail_{{$view_type}}_{{$line->_id}}" style="display: none;">
  <table class="main form">
    <tr>
      <th class="title">{{tr}}CPlanificationSysteme-Planifications details{{/tr}}</th>
    </tr>
    {{foreach from=$line->_ref_prises item=_prise}}
      <tr>
        <td>
          <span class="texticon texticon-hopital me-no-border" style="font-size: 1em;">
            {{$_prise->_view}}
          </span>
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>
