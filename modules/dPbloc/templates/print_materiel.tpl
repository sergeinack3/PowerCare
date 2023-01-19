{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=commande_mat}}

<script>
  refreshLists = function() {
    location.reload();
  };
  Main.add(function() {
    Control.Tabs.create('tabs-commande_mat', true);
  });
</script>

<h1>
  {{mb_label class=COperation field=materiel}}
  du {{mb_value object=$filter field=_date_min}}
  au {{mb_value object=$filter field=_date_max}}
</h1>

<ul id="tabs-commande_mat" class="control_tabs">
  {{foreach from=$operations key=commande_mat item=_operations}}
    <li>
      <a href="#commande_mat_{{$commande_mat}}" {{if !$_operations|@count}}class="empty"{{/if}}>
        {{tr}}CCommandeMaterielOp.etat.{{$commande_mat}}{{/tr}}
        <small>({{$_operations|@count}})</small>
      </a>
    </li>
  {{/foreach}}
</ul>

<span style="float: right">{{$blocs_names}}</span>

{{foreach from=$operations key=commande_mat item=_operations}}
{{mb_include template=inc_list_materiel}}
{{/foreach}}
