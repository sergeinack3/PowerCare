{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-commande_mat', true);
  });
</script>

<ul id="tabs-commande_mat" class="control_tabs">
  {{foreach from=$operations key=commande_mat item=_operations}}
    <li>
      <a href="#commande_mat_{{$commande_mat}}" {{if !$_operations|@count}}class="empty"{{/if}}>
        {{tr}}CCommandeMaterielOp.etat.{{$commande_mat}}.title{{/tr}}
        <small>({{$_operations|@count}})</small>
      </a>
    </li>
  {{/foreach}}
</ul>

{{foreach from=$operations key=commande_mat item=_operations}}
  {{mb_include template=inc_list_materiel}}
{{/foreach}}