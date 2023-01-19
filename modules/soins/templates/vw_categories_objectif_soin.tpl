{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=soins script=CategorieObjectifSoin ajax=1}}

<script>
  Main.add(function() {
    CategorieObjectifSoin.refreshList();
  });
</script>

<button class="new me-primary" onclick="CategorieObjectifSoin.edit();">
  {{tr}}CObjectifSoinCategorie-action-create{{/tr}}
</button>

<button id="buttonShowInactive" data-show="1" class="search"
        style="float:right;margin-right:5px;{{if !$countInactive}}display:none{{/if}}" onclick="CategorieObjectifSoin.toggleInactive(this, '{{$countInactive}}');">
  {{tr}}CObjectifSoinCategorie-show_inactive{{/tr}}({{$countInactive}})
</button>

<table class="main">
    <tr>
      <td id="listCategories">
      </td>
    </tr>
</table>