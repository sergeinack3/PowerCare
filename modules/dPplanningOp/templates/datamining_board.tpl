{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
Main.add(function() {
  Datamining.auto.delay(2);
});
</script>

<h2>{{tr}}Datamining{{/tr}}</h2>

<div class="small-info">
  Ce tableau de bord permet de connaitre le statut du datamining des opérations,
  permettant l'extraction de statistiques d'activité à hautes performances.
</div>

<table class="tbl" style="text-align: center;">
  <tr>
    <th class="title" colspan="4">Décomptes des opérations</th>
  </tr>

  <tr>
    <th style="width: 25%;">Toutes</th>
    <th style="width: 25%;">A miner (< J-28)</th>
    <th style="width: 25%;">A consolider (< J)</th>
    <th style="width: 25%;">A consolider (< J+28)</th>
  </tr>

  <tr>
    <td><strong>{{$counts.overall|integer    }}</strong></td>
    <td><strong>{{$counts.tobemined|integer  }}</strong></td>
    <td><strong>{{$counts.toberemined|integer  }}</strong></td>
    <td><strong>{{$counts.tobepostmined|integer}}</strong></td>
  </tr>
</table>

<hr />

<table class="tbl" style="text-align: center;">
  <tr>
    <th class="title" colspan="10">
      <span style="float: right;">
        <label>
          <input type="checkbox" id="automine" {{if $automine}} checked="checked" {{/if}} />
          auto
        </label>
        par
        <input type="text" id="limit" size="4" value="{{$limit}}">
      </span>
      Statut des explorateurs d'opération
    </th>
  </tr>
  <tr>
    <th>Mineur</th>
    <th>Dernière exploration</th>
    <th colspan="2">Non encore explorées (< J-28)</th>
    <th colspan="2">Non encore consolidées (< J)</th>
    <th colspan="2">Non encore consolidées (< J+28)</th>
  </tr>

  {{foreach from=$miners item=_miner}}
  <tr>
    <td>
      <strong>{{tr}}{{$_miner->_class}}{{/tr}}</strong>
    </td>
    <td>
      {{mb_value object=$_miner field=date}}
    </td>
    <td>
      {{$_miner->_count_unmined|integer}}
    </td>
    <td class="narrow">
      <button type="button" class="change notext compact oneclick" onclick="Datamining.mine('{{$_miner->_class}}', 'mine')">
        {{tr}}Do{{/tr}}
      </button>
    </td>
    <td>
      {{$_miner->_count_unremined|integer}}
    </td>
    <td class="narrow">
      <button type="button" class="change notext compact oneclick" onclick="Datamining.mine('{{$_miner->_class}}', 'remine')">
        {{tr}}Do{{/tr}}
      </button>
    </td>
    <td>
      {{$_miner->_count_unpostmined|integer}}
    </td>
    <td class="narrow">
      <button type="button" class="change notext compact oneclick" onclick="Datamining.mine('{{$_miner->_class}}', 'postmine')">
        {{tr}}Do{{/tr}}
      </button>
    </td>
  </tr>

  {{foreachelse}}
  <tr>
    <td class="empty">{{tr}}COperationMiner.none{{/tr}}<</td>
  </tr>
  {{/foreach}}

</table>
