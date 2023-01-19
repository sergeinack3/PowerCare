{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=type}}
    </th>
    <td>
      {{mb_field object=$sejour field=type onchange="DHE.sejour.setAdmissionDates(this, 'edit');"}}
    </td>
  </tr>
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=type_pec}}
    </th>
    <td>
      {{mb_field object=$sejour field=type_pec typeEnum=radio onchange="DHE.sejour.syncView(this); DHE.sejour.changeTypePec();"}}
    </td>
  </tr>
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=modalite}}
    </th>
    <td>
      {{mb_field object=$sejour field=modalite typeEnum=radio onchange="DHE.sejour.syncView(this);"}}
    </td>
  </tr>



  {{assign var=use_cpi value='dPplanningOp CSejour use_charge_price_indicator'|gconf}}
  {{if $use_cpi != 'no'}}
    <tr>
      <th class="halfPane">
        {{mb_label object=$sejour field=charge_id}}
      </th>
      <td>
        <select name="charge_id" class="ref{{if $use_cpi == 'obl'}} notNull{{/if}}" id="" onchange="DHE.sejour.syncView(this);">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$cpis item=_cpi}}
            <option value="{{$_cpi->_id}}"{{if $sejour->charge_id == $_cpi->_id}} selected{{/if}}
                    data-type="{{$_cpi->type}}" data-type_pec="{{$_cpi->type_pec}}" data-hospit_de_jour="{{$_cpi->hospit_de_jour}}">
              {{$_cpi|truncate:50:"...":false}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  {{/if}}

  {{if "dPplanningOp CSejour fields_display show_discipline_tarifaire"|gconf}}
    <tr>
      <th class="halfPane">
        {{mb_label object=$sejour field=discipline_id}}
      </th>
      <td>
        {{mb_field object=$sejour field=discipline_id onchange="DHE.sejour.syncView(this, null, \$T('CSejour-' + this.name + '-desc') + ': ' + \$V(this.form.discipline_id_autocomplete_view));" form='sejourEdit' autocomplete='true,1,50,true,true' view="discipline_id_autocomplete_view"}}
        <button type="button" class="cancel notext" onclick="DHE.emptyField(this.form.discipline_id);">{{tr}}Empty{{/tr}}</button>
      </td>
    </tr>
  {{/if}}

  {{* @todo: Voir avec Aurélie pour gérer les UFs *}}
  {{*
    * Explications de la nouvelle modale des UFs :
    *  - Les tpl inc_vw_ufs_object affiche les UFs présélectionnable en fonction des contextes
    *    (séjour, service, chambre ou lit pour les ufs d'hébergement ou les unité de soins)
    *   (séjour, fonction, praticien, praticien replacant pour les ufs médicale)
    *  - Le tpl inc_options_ufs_context_form affiche les différentes options, en fonction des contextes (+ un champ de sélection d'autre ufs)
    *}}

  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=forfait_se}}
    </th>
    <td>
      {{mb_field object=$sejour field=forfait_se onchange="DHE.sejour.syncViewFlag(this);"}}
    </td>
  </tr>
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=forfait_sd}}
    </th>
    <td>
      {{mb_field object=$sejour field=forfait_sd onchange="DHE.sejour.syncViewFlag(this);"}}
    </td>
  </tr>
  <tr>
    <th class="halfPane">
      {{mb_label object=$sejour field=hospit_de_jour}}
    </th>
    <td>
      {{mb_field object=$sejour field=hospit_de_jour onchange="DHE.sejour.syncViewFlag(this);"}}
    </td>
  </tr>
</table>
