{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    PairEffect.initGroup('tree-content');
  });
</script>

<table class="main">
  <tr>
    <!-- Affichage des catalogues -->
    <td class="halfPane">
      {{foreach from=$listCatalogues item=_catalogue}}
        {{mb_include module=labo template=tree_catalogues}}
      {{/foreach}}
    </td>
  </tr>
</table>