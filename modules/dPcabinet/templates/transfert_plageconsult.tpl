{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  var PlageConsult = {
    changePlage: Prototype.K
  }
</script>

<!-- Filtre -->
<form name="Find" method="get" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="{{$a}}" />
  <input type="hidden" name="dialog" value="{{$dialog}}" />
  <input type="hidden" name="new" value="1" />

  <table class="form">
    <tr>
      <th class="title" colspan="3">Recherche de plages</th>
    </tr>

    <tr>
      <th style="width: 50%;">{{mb_label object=$filter field=chir_id}}</th>
      <td style="width: 50%;">
        <select name="chir_id" class="{{$filter->_props.chir_id}}">
          <option value="">&mdash; Choisir un praticien</option>
          {{mb_include module=mediusers template=inc_options_mediuser selected=$filter->chir_id list=$praticiens}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field=_date_min}}</th>
      <td>{{mb_field object=$filter field=_date_min form=Find register=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$filter field=_date_max}}</th>
      <td>{{mb_field object=$filter field=_date_max form=Find register=true}}</td>
    </tr>

    <tr>
      <td class="button" colspan="3">
        <button class="search" type="submit">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

{{if $plages|@count}}
  <!-- Tranfert -->
  <form name="Transfert" action="?m=cabinet" method="post" onsubmit="return checkForm(this)">
    <input type="hidden" name="dosql" value="do_plageconsult_transfert" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="plageconsult_id" value="" />
    <input type="hidden" name="_old_chir_id" value="{{$filter->chir_id}}" />
    <input type="hidden" name="_date_min" value="{{$filter->_date_min}}" />
    <input type="hidden" name="_date_max" value="{{$filter->_date_max}}" />

    <table class="form">
      <tr>
        <th class="category" colspan="10">Transférer {{$plages|@count}} ces plages de consultations</th>
      </tr>

      <tr>
        <th style="width: 50%">{{mb_label object=$filter field=chir_id}}</th>
        <td style="width: 50%">
          <select name="chir_id" class="{{$filter->_props.chir_id}}">
            <option value="">&mdash; Choisir un praticien</option>
            {{mb_include module=mediusers template=inc_options_mediuser list=$praticiens}}
          </select>
        </td>
      </tr>

      <tr>
        <td class="button" colspan="10">
          <button class="submit" type="submit">Transférer</button>
        </td>
      </tr>
    </table>
  </form>
{{/if}}

<!-- Résultats -->
<table class="tbl" style="text-align: center;	">
  {{if $filter->chir_id}}
  <tr>
    <th class="category" colspan="100">
      {{$plages|@count}} plages de consultation trouvées
    </th>
  </tr>
  {{/if}}

  <tr>
    <th colspan="3">{{mb_title object=$filter field=date}}</th>
    <th>{{mb_title object=$filter field=debut}}</th>
    <th>{{mb_title object=$filter field=fin}}</th>
    <th>{{mb_title object=$filter field=libelle}}</th>
  </tr>

  <tbody>
    {{foreach from=$plages item=_plage}}
    <tr>
      <td>{{mb_ditto name=year value=$_plage->date|date_format:"%Y"}}</td>
      <td>{{mb_ditto name=month value=$_plage->date|date_format:"%B"}}</td>
      <td>{{mb_include module=cabinet template=inc_plage_etat}}</td>
      <td>{{mb_value object=$_plage field=debut}}</td>
      <td>{{mb_value object=$_plage field=fin}}</td>
      <td>{{mb_value object=$_plage field=libelle}}</td>
    </tr>
    {{/foreach}}
  </tbody>
</table>