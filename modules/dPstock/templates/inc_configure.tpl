{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}

  <table class="form" style="table-layout: fixed;">
    {{assign var="var" value="host_group_id"}}
    <tr>
      <th>{{tr}}config-{{$m}}-{{$var}}{{/tr}}</th>
      <td>
        {{assign var=name value="$m[$var]"}}

        <select name="{{$name}}">
          <option value="">&ndash; Chaque établissement gère ses stocks</option>
          {{foreach from=$groups_list item=_group}}
            <option value="{{$_group->_id}}" {{if $conf.$m.$var == $_group->_id}}selected{{/if}}>{{$_group}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tbody style="display: none;">
    <tr>
      <th class="category" colspan="2">{{tr}}General{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_bool class=general var=unite_disp}}
    </tbody>

    {{assign var="class" value="CProductOrder"}}
    <tr>
      <th class="category" colspan="2">{{tr}}{{$class}}{{/tr}}</th>
    </tr>
    {{mb_include module=system template=inc_config_str var=order_number_format cssClass="code product_order"}}
    {{mb_include module=system template=inc_config_bool var=order_number_contextual}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div class="big-info">
  Les caractères suivants sont utilisés pour spécifier le format du numéro de commande :
  <ul>
    <li>%id - identifiant unique de la commande en base de données</li>
    <li>%a - nom abrégé du jour de la semaine (local)</li>
    <li>%A - nom complet du jour de la semaine (local)</li>
    <li>%b - nom abrégé du mois (local)</li>
    <li>%B - nom complet du mois (local)</li>
    <li>%c - représentation préférée pour les dates et heures, en local</li>
    <li>%C - numéro de siècle (l&#039;année, divisée par 100 et arrondie entre 00 et 99)</li>
    <li>%d - jour du mois en numérique (intervalle 01 à 31)</li>
    <li>%D - identique à %m/%d/%y</li>
    <li>%e - numéro du jour du mois. Les chiffres sont précédés d&#039;un espace (de &#039; 1&#039; à &#039;31&#039;)</li>
    <li>%g - identique à %G, sur 2 chiffres</li>
    <li>%G - L&#039;année sur 4 chiffres correspondant au numéro de semaine (voir %V). Même format et valeur que %Y, excepté que si le
      numéro de la semaine appartient à l&#039;année précédente ou suivante, l&#039;année courante sera utilisé à la place
    </li>
    <li>%h - identique à %b</li>
    <li>%H - heure de la journée en numérique, et sur 24-heures (intervalle de 00 à 23)</li>
    <li>%I - heure de la journée en numérique, et sur 12- heures (intervalle 01 à 12)</li>
    <li>%j - jour de l&#039;année, en numérique (intervalle 001 à 366)</li>
    <li>%m - mois en numérique (intervalle 1 à 12)</li>
    <li>%M - minute en numérique</li>
    <li>%n - caractère de nouvelle ligne</li>
    <li>%p - soit `am&#039; ou `pm&#039; en fonction de l&#039;heure absolue, ou en fonction des valeurs enregistrées en local</li>
    <li>%r - l&#039;heure au format a.m. et p.m</li>
    <li>%R - l&#039;heure au format 24h</li>
    <li>%S - secondes en numérique</li>
    <li>%t - tabulation</li>
    <li>%T - l&#039;heure actuelle (égal à %H:%M:%S)</li>
    <li>%u - le numéro de jour dans la semaine, de 1 à 7. (1 représente Lundi)</li>
    <li>%U - numéro de semaine dans l&#039;année, en considérant le premier dimanche de l&#039;année comme le premier jour de la
      première semaine
    </li>
    <li>%V - le numéro de semaine comme défini dans l&#039;ISO 8601:1988, sous forme décimale, de 01 à 53. La semaine 1 est la première
      semaine qui a plus de 4 jours dans l&#039;année courante, et dont Lundi est le premier jour. (Utilisez %G ou %g pour les éléments
      de l&#039;année qui correspondent au numéro de la semaine pour le timestamp donné.)
    </li>
    <li>%W - numéro de semaine dans l&#039;année, en considérant le premier lundi de l&#039;année comme le premier jour de la première
      semaine
    </li>
    <li>%w - jour de la semaine, numérique, avec Dimanche = 0</li>
    <li>%x - format préféré de représentation de la date sans l&#039;heure</li>
    <li>%X - format préféré de représentation de l&#039;heure sans la date</li>
    <li>%y - l&#039;année, numérique, sur deux chiffres (de 00 à 99)</li>
    <li>%Y - l&#039;année, numérique, sur quatre chiffres</li>
    <li>%Z ou %z - fuseau horaire, ou nom ou abréviation</li>
    <li>%% - un caractère `%&#039; littéral</li>
  </ul>
</div>