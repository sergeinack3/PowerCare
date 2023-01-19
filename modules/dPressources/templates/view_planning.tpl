{{*
 * @package Mediboard\Ressources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function alertAction() {
    return confirm("Voulez-vous confirmer votre action ?");
  }

  Main.add(function () {
    {{if $isprat}}
    PairEffect.initGroup("effectCategory");
    {{/if}}
    Calendar.regField(getForm("changeDate").debut, null, {noView: true});
  });
</script>

<table class="main">
  <tr>
    <th class="title">
      <a href="?m={{$m}}&debut={{$prec}}">&lt;&lt;&lt;</a>
      semaine du {{$debut|date_format:$conf.longdate}}
      <form action="?" name="changeDate" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <input type="hidden" name="debut" class="date" value="{{$debut}}" onchange="this.form.submit()" />
      </form>
      <a href="?m={{$m}}&debut={{$suiv}}">&gt;&gt;&gt;</a>
    </th>
    <th class="title">Votre compte</th>
  </tr>
  <tr>
    <td class="halfPane">
      <table width="100%" id="weeklyPlanning">
        <tr>
          <th></th>
          {{foreach from=$plages|smarty:nodefaults key=curr_day item=plagesPerDay}}
            <th scope="col" style="width: {{math equation="x/y" x=100 y=$plages|@count}}%">{{$curr_day|date_format:"%A %d"}}</th>
          {{/foreach}}
        </tr>
        {{foreach from=$listHours|smarty:nodefaults item=curr_hour}}
          <tr>
            <th scope="row">{{$curr_hour}}h</th>
            {{foreach from=$plages key=curr_day item=plagesPerDay}}
              {{assign var="isNotIn" value=1}}
              {{foreach from=$plagesPerDay item=curr_plage}}
                {{if $curr_plage->_hour_deb == $curr_hour}}
                  {{if ($curr_plage->_state == $curr_plage|const:'PAYED') && ($curr_plage->prat_id != $app->user_id)}}
                    <td style="background-color: {{$curr_plage|const:'OUT'}}"
                        rowspan="{{$curr_plage->_hour_fin-$curr_plage->_hour_deb}}">
                      {{else}}
                    <td style="vertical-align:middle; text-align:center; background-color:{{$curr_plage->_state}}" rowspan="{{$curr_plage->_hour_fin-$curr_plage->_hour_deb}}">
                  {{/if}}
                  <span {{if $curr_plage->prat_id == $app->user_id}} style="font-weight: bold; color: #060;" {{/if}}>
                    {{if $curr_plage->libelle}}
                      {{$curr_plage->libelle}}
                      <br />
                    {{/if}}

                    {{$curr_plage->tarif|currency}}<br />
                    {{$curr_plage->debut|date_format:"%H"}}h - {{$curr_plage->fin|date_format:"%H"}}h
                    
                    {{if $curr_plage->prat_id}}
                      <br />
                      Dr {{$curr_plage->_ref_prat->_view}}
                    {{/if}}
                  </span>
                  <br />
                  {{if $isprat && (($curr_plage->_state == $curr_plage|const:'FREE') || (($curr_plage->_state == $curr_plage|const:'BUSY') && ($curr_plage->prat_id == $app->user_id)))}}
                    <form name="editPlage{{$curr_plage->plageressource_id}}" action="?m={{$m}}" method="post"
                          onSubmit=" return alertAction()">
                      <input type='hidden' name='dosql' value='do_plageressource_aed' />
                      <input type='hidden' name='del' value='0' />
                      <input type='hidden' name='plageressource_id' value='{{$curr_plage->plageressource_id}}' />
                      {{if $curr_plage->_state == $curr_plage|const:'FREE'}}
                        <input type='hidden' name='prat_id' value='{{$app->user_id}}' />
                        <button class="tick" type="submit">Réserver</button>
                      {{else}}
                        <input type='hidden' name='prat_id' value='' />
                        <button class="cancel" type="submit">Annuler</button>
                      {{/if}}
                    </form>
                  {{/if}}
                  </td>
                {{/if}}
                {{if ($curr_plage->_hour_deb <= $curr_hour) && ($curr_plage->_hour_fin > $curr_hour)}}
                  {{assign var="isNotIn" value=0}}
                {{/if}}
              {{/foreach}}
              {{if $isNotIn}}
                <td class="empty hour_start"></td>
              {{/if}}
            {{/foreach}}
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td>
      <table class="form me-margin-top-0">
        {{if $isprat}}
          <tr id="impayes-trigger">
            <th style="background:#ddf">Plages à régler</th>
            <td>{{$compte.impayes.total}} ({{$compte.impayes.somme|currency}})</td>
          </tr>
          <tbody class="effectCategory" id="impayes">
          {{foreach from=$compte.impayes.plages item=curr_plage}}
            <tr>
              <td colspan="2" class="text">
                <a href="?m={{$m}}&debut={{$curr_plage->date|iso_date}}">
                  {{$curr_plage->date|date_format:$conf.longdate}} &mdash;
                  {{if $curr_plage->libelle}}
                    {{$curr_plage->libelle}} &mdash;
                  {{/if}}
                  de {{$curr_plage->debut|date_format:"%H"}}h à {{$curr_plage->fin|date_format:"%H"}}h &mdash;
                  {{$curr_plage->tarif|currency}}
                </a>
              </td>
            </tr>
            {{foreachelse}}
            <tr>
              <td colspan="2" class="text empty">
                Aucun
              </td>
            </tr>
          {{/foreach}}
          </tbody>
          <tr id="inf15-trigger">
            <th style="background:#ddf">Plages réservées et bloquées</th>
            <td>{{$compte.inf15.total}} ({{$compte.inf15.somme|currency}})</td>
          </tr>
          <tbody class="effectCategory" id="inf15">
          {{foreach from=$compte.inf15.plages item=curr_plage}}
            <tr>
              <td colspan="2" class="text">
                <a href="?m={{$m}}&debut={{$curr_plage->date|iso_date}}">
                  {{$curr_plage->date|date_format:$conf.longdate}} &mdash;
                  {{if $curr_plage->libelle}}
                    {{$curr_plage->libelle}} &mdash;
                  {{/if}}
                  de {{$curr_plage->debut|date_format:"%H"}}h à {{$curr_plage->fin|date_format:"%H"}}h &mdash;
                  {{$curr_plage->tarif|currency}}
                </a>
              </td>
            </tr>
            {{foreachelse}}
            <tr>
              <td colspan="2" class="text empty">
                Aucun
              </td>
            </tr>
          {{/foreach}}
          </tbody>
          <tr id="sup15-trigger">
            <th style="background:#ddf">Plages réservées à plus de 15 jours</th>
            <td>{{$compte.sup15.total}} ({{$compte.sup15.somme|currency}})</td>
          </tr>
          <tbody class="effectCategory" id="sup15">
          {{foreach from=$compte.sup15.plages item=curr_plage}}
            <tr>
              <td colspan="2" class="text">
                <a href="?m={{$m}}&debut={{$curr_plage->date|iso_date}}">
                  {{$curr_plage->date|date_format:$conf.longdate}} &mdash;
                  {{if $curr_plage->libelle}}
                    {{$curr_plage->libelle}} &mdash;
                  {{/if}}
                  de {{$curr_plage->debut|date_format:"%H"}}h à {{$curr_plage->fin|date_format:"%H"}}h &mdash;
                  {{$curr_plage->tarif|currency}}
                </a>
              </td>
            </tr>
            {{foreachelse}}
            <tr>
              <td colspan="2" class="text empty">
                Aucun
              </td>
            </tr>
          {{/foreach}}
          </tbody>
        {{/if}}
        <tr>
          <th colspan="2" class="category">Légende</th>
        </tr>
        <tr>
          <th style="background:{{$plage|const:'OUT'}}"></th>
          <td class="text">Plage terminée</td>
        </tr>
        <tr>
          <th style="background:{{$plage|const:'FREE'}}"></th>
          <td class="text">Plage libre</td>
        </tr>
        <tr>
          <th style="background:{{$plage|const:'FREEB'}}"></th>
          <td class="text">Plage libre non réservable (dans plus d'1 mois)</td>
        </tr>
        <tr>
          <th style="background:{{$plage|const:'BUSY'}}"></th>
          <td class="text">Plage réservée (echéance dans plus de 15 jours)</td>
        </tr>
        <tr>
          <th style="background:{{$plage|const:'BLOCKED'}}"></th>
          <td class="text">Plage bloquée (échéance dans moins de 15 jours)</td>
        </tr>
        <tr>
          <th style="background:{{$plage|const:'PAYED'}}"></th>
          <td class="text">Plage réglée</td>
        </tr>
        <tr>
          <th style="font-weight: bold; color: #060;">Dr {{$prat}}</th>
          <td class="text">Plage vous appartenant</td>
        </tr>
      </table>
    </td>
  </tr>
</table>