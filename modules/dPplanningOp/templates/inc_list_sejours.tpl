{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=padding value="4em"}}

<table class="main" style="border-collapse: collapse;">
  {{foreach from=$sejours_by_NDA key=NDA item=_sejours}}
    <tr id="NDAEffect-{{$NDA}}-trigger">
      <td>
        <span class="texticon" style="font-weight: bold;">{{if $NDA}}{{$NDA}}{{else}}{{tr}}CSejour-no_NDA{{/tr}}{{/if}}</span>
        <script>
          Main.add(function() {
            new PairEffect("NDAEffect-{{$NDA}}", {bStartVisible: true, bStoreInCookie: false});
          });
        </script>
      </td>
    </tr>
    <tbody id="NDAEffect-{{$NDA}}">
      {{foreach from=$_sejours item=object}}
        {{mb_include module=patients template=CSejour_event}}
      {{/foreach}}
    </tbody>
  {{foreachelse}}
    <tr>
      <td class="empty">
        {{tr}}CSejour.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
