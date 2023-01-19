{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  popupEtabDetails = function(etab_id) {
    var url = new Url('openData', 'vw_etab_details');
    url.addParam('etab_id', etab_id);
    url.requestModal('90%', '70%');
  }
</script>

{{mb_include module=system template=inc_pagination total=$total current=$start step=$step change_page='changePage'}}

<table class="main tbl">
  <tr>
    <th></th>
    <th>{{mb_label class=CHDEtablissement field=finess}}</th>
    <th>{{mb_label class=CHDEtablissement field=raison_sociale}}</th>
    <th>{{mb_label class=CHDEtablissement field=champ_pmsi}}</th>
    <th>{{mb_label class=CHDEtablissement field=cat}}</th>
    <th>{{mb_label class=CHDEtablissement field=taille_mco}}</th>
    <th>{{mb_label class=CHDEtablissement field=taille_m}}</th>
    <th>{{mb_label class=CHDEtablissement field=taille_c}}</th>
    <th>{{mb_label class=CHDEtablissement field=taille_o}}</th>
    <th>{{mb_label class=CHDIdentite field=nb_lits_med}}</th>
    <th>{{mb_label class=CHDIdentite field=nb_lits_chir}}</th>
    <th>{{mb_label class=CHDIdentite field=nb_lits_obs}}</th>
  </tr>

  {{foreach from=$etabs item=_etab}}
    <tr>
      <td class="narrow">
        <button type="button" class="lookup notext" onclick="popupEtabDetails('{{$_etab.hd_etablissement_id}}');">
          {{tr}}mod-openData-hd-display-etab{{/tr}}
        </button>
      </td>
      <td width="9em">{{$_etab.finess}}</td>
      <td align="right">{{$_etab.raison_sociale}}</td>
      <td align="right">{{$_etab.champ_pmsi}}</td>
      <td align="right">{{$_etab.cat}}</td>
      <td align="right">{{$_etab.taille_mco}}</td>
      <td align="right">{{$_etab.taille_m}}</td>
      <td align="right">{{$_etab.taille_c}}</td>
      <td align="right">{{$_etab.taille_o}}</td>
      <td align="right">{{$_etab.nb_lits_med}}</td>
      <td align="right">{{$_etab.nb_lits_chir}}</td>
      <td align="right">{{$_etab.nb_lits_obs}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">
        {{tr}}CHDEtablissement.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>