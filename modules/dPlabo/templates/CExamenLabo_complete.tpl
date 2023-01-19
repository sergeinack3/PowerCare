{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="examen" value=$object}}

<table class="form">

  <tr>
    <th class="title" colspan="2">
      {{$examen->_view}}
    </th>
  </tr>

  <tr>
    <th class="category" colspan="2">{{tr}}mod-dPlabo-inc-acc_infos{{/tr}}</th>
  </tr>

  {{foreach from=$examen->_ref_catalogues item="_catalogue"}}
    <tr>
      <th>{{tr}}CExamen-catalogue-{{$_catalogue->_level}}{{/tr}}</th>
      <td>{{$_catalogue->_view}}</td>
    </tr>
  {{/foreach}}

  <tr>
    <th>{{mb_label object=$examen field="type"}}</th>
    <td>
      {{mb_value object=$examen field="type"}}
      {{if $examen->type == "num" || $examen->type == "float"}}
        : {{$examen->unite}}
        ( {{$examen->min}} &ndash; {{$examen->max}} )
      {{/if}}
    </td>
  </tr>

  <tr>
    <th>{{tr}}{{$examen->_class}}-application{{/tr}}</th>
    <td>
      du {{$examen->deb_application|date_format:$conf.date|default:"-"}}
      au {{$examen->fin_application|date_format:$conf.date|default:"-"}}
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="realisateur"}}</th>
    <td>
      {{assign var="realisateur" value=$examen->_ref_realisateur}}
      {{if $realisateur->_id}}
        {{assign var="function" value=$realisateur->_ref_function}}
        {{$function->_ref_group->_view}}
        &mdash;
        <div class="mediuser" style="display: inline; border-color: #{{$function->color}};">{{$function->_view}}</div>
        &mdash; {{$realisateur->_view}}
      {{else}}
        <em>Indéterminé</em>
      {{/if}}
    </td>
  </tr>

  <tr>
    <th class="category" colspan="2">{{tr}}mod-dPlabo-inc-acc_realisation{{/tr}}</th>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="applicabilite"}}</th>
    <td>{{mb_value object=$examen field="applicabilite"}}</td>
  </tr>

  <tr>
    <th>Limites d'âge</th>
    <td>
      de {{$examen->age_min|default:"-"}}
      à {{$examen->age_max|default:"-"}} ans
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="technique"}}</th>
    <td class="text">{{mb_value object=$examen field="technique"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="materiel"}}</th>
    <td class="text">{{mb_value object=$examen field="materiel"}}</td>
  </tr>

  <tr>
    <th>
      {{mb_label object=$examen field="methode_prelevement"}}<br />
      (type: {{mb_value object=$examen field="type_prelevement"}})
    </th>
    <td class="text">{{mb_value object=$examen field="methode_prelevement"}}</td>
  </tr>

  <tr>
    <th class="category" colspan="2">{{tr}}mod-dPlabo-inc-acc_conservation{{/tr}}</th>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="conservation"}}</th>
    <td class="text">{{mb_value object=$examen field="conservation"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="temps_conservation"}}</th>
    <td>{{mb_value object=$examen field="temps_conservation"}} jours</td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="quantite_prelevement"}}</th>
    <td>
      {{mb_value object=$examen field="quantite_prelevement"}}
      {{mb_value object=$examen field="unite_prelevement"}}
    </td>
  </tr>

  <tr>
    <th>Jours d'exécution</th>
    <td>
      {{if $examen->execution_lun}}{{mb_label object=$examen field="execution_lun"}}{{/if}}
      {{if $examen->execution_lun}}{{mb_label object=$examen field="execution_mar"}}{{/if}}
      {{if $examen->execution_lun}}{{mb_label object=$examen field="execution_mer"}}{{/if}}
      {{if $examen->execution_lun}}{{mb_label object=$examen field="execution_jeu"}}{{/if}}
      {{if $examen->execution_lun}}{{mb_label object=$examen field="execution_ven"}}{{/if}}
      {{if $examen->execution_lun}}{{mb_label object=$examen field="execution_sam"}}{{/if}}
      {{if $examen->execution_lun}}{{mb_label object=$examen field="execution_dim"}}{{/if}}
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="duree_execution"}}</th>
    <td colspan="7">{{mb_value object=$examen field="duree_execution"}} heures</td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="remarques"}}</th>
    <td colspan="7">{{mb_value object=$examen field="remarques"}}</td>
  </tr>

</table>
