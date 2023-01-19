{{*
 * @package Mediboard\Ressources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  function checkFormPrint() {
    var form = document.paramFrm;
    if (!checkForm(form)) {
      return false;
    }
    popRapport();
  }

  function popRapport() {
    var form = document.paramFrm;
    var url = new Url();
    url.setModuleAction("dPressources", "print_rapport");
    url.addElement(form._date_min);
    url.addElement(form._date_max);
    url.addElement(form.type);
    url.addElement(form.prat_id);
    url.popup(700, 550, "Rapport");
  }

  Main.add(function () {
    PairEffect.initGroup("effectPlage");
  });
</script>

<table class="main">
  <tr>
    <td class="halfPane">
      <table class="tbl">
        <tr>
          <th colspan="3" class="title">
            Plages en attente de paiement &mdash; {{$today|date_format:$conf.longdate}}
          </th>
        </tr>
        <tr>
          <th>Praticien</th>
          <th>Quantité</th>
          <th>Montant</th>
        </tr>
        {{foreach from=$list item=curr_prat}}
          <tr id="plages{{$curr_prat.prat_id}}-trigger">
            <td>Dr {{$curr_prat.praticien->_view}}</td>
            <td>{{$curr_prat.total}} plage(s)</td>
            <td>{{$curr_prat.somme|currency}}</td>
          </tr>
          <tbody class="effectPlage" id="plages{{$curr_prat.prat_id}}">
          {{foreach from=$curr_prat.plages item=curr_plage}}
            <tr>
              <td>
                <form name="editPlage{{$curr_plage->plageressource_id}}" action="?m={{$m}}" method="post">
                  <input type="hidden" name="dosql" value="do_plageressource_aed" />
                  <input type="hidden" name="del" value="0" />
                  <input type="hidden" name="plageressource_id" value="{{$curr_plage->plageressource_id}}" />
                  <input type="hidden" name="paye" value="1" />
                  <button type="submit" class="submit">Valider le paiement</button>
                </form>
              </td>
              <td>{{$curr_plage->date|date_format:$conf.longdate}}</td>
              <td>{{$curr_plage->tarif|currency}}</td>
            </tr>
          {{/foreach}}
          </tbody>
        {{/foreach}}
        <tr>
          <th>{{$total.prat}} praticien(s)</th>
          <th>{{$total.total}} plage(s)</th>
          <th>{{$total.somme|currency}}</th>
      </table>

    </td>
    <td>

      <form name="paramFrm" action="?m=dPressources" method="post" onsubmit="return checkFormPrint()">
        <table class="form">
          <tr>
            <th class="title" colspan="2">Edition des rapports</th>
          </tr>
          <tr>
            <td>{{mb_label object=$filter field="_date_min"}}</td>
            <td>{{mb_field object=$filter field="_date_min" form="paramFrm" canNull="false" register=true}} </td>
          </tr>
          <tr>
            <td>{{mb_label object=$filter field="_date_max"}}</td>
            <td>{{mb_field object=$filter field="_date_max" form="paramFrm" canNull="false" register=true}}</td>
          </tr>
          <tr>
            <td>{{mb_label object=$filter field="paye"}}</td>
            <td>
              <select name="type">
                <option value="0">Plages non payées</option>
                <option value="1">Plages payées</option>
              </select>
          </tr>
          <tr>
            <td>{{mb_label object=$filter field="prat_id"}}</td>
            <td>
              <select name="prat_id">
                {{mb_include module=mediusers template=inc_options_mediuser list=$listPrats}}
              </select>
            </td>
          </tr>
          <tr>
            <td colspan="2" class="button">
              <button class="print" type="button" onclick="checkFormPrint()">Afficher</button>
            </td>
          </tr>
        </table>
      </form>

    </td>
  </tr>
</table>