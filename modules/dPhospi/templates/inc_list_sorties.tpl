{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=show_duree_preop value=$conf.dPplanningOp.COperation.show_duree_preop}}
{{assign var=show_age_sexe_mvt value="dPhospi mouvements show_age_sexe_mvt"|gconf}}
{{assign var=show_hour_anesth_mvt value="dPhospi mouvements show_hour_anesth_mvt"|gconf}}
{{assign var=show_retour_mvt value="dPhospi mouvements show_retour_mvt"|gconf}}
{{assign var=show_collation_mvt value="dPhospi mouvements show_collation_mvt"|gconf}}
{{assign var=show_sortie_mvt value="dPhospi mouvements show_sortie_mvt"|gconf}}

{{mb_script module=dPadmissions script=admissions ajax=true}}

<script>
  $('count_{{$type}}_{{$type_mouvement}}').update('(' + '{{$update_count}}' + ')');
  Main.add(function () {
    {{if $type != "mouvements"}}
    controlTabs = Control.Tabs.create('tabs-edit-mouvements-{{$type}}_{{$type_mouvement}}', true);
    {{else}}
    controlTabs = Control.Tabs.create('tabs-edit-mouvements-{{$type}}', true);
    {{/if}}
    {{if $isImedsInstalled}}
    ImedsResultsWatcher.loadResults();
    {{/if}}
  });
</script>

{{assign var=update_splitted value="/"|explode:$update_count}}

{{if $type == "mouvements"}}
  <script>
    refreshList_mouvements = function (order_col, order_way) {
      refreshList(order_col, order_way, 'mouvements');
    }
  </script>
  <ul id="tabs-edit-mouvements-{{$type}}" class="control_tabs me-margin-top-0 me-no-border-top me-no-border-right me-no-border-left">
    <li>
      <a href="#places-{{$type}}_entrants">Entrants
        <small id="count_dep_entrants">({{$update_splitted.0}})</small>
      </a>
    </li>
    <li>
      <a href="#places-{{$type}}_sortants">Sortants
        <small id="count_dep_sortants">({{$update_splitted.1}})</small>
      </a>
    </li>
  </ul>
  <div id="places-{{$type}}_entrants" style="display: none;" class="me-no-border me-no-align">
    <table class="tbl me-no-align me-no-box-shadow">
      <tr class="only-printable">
        <th class="title text" colspan="100">
          Déplacements entrants ({{$dep_entrants|@count}})
          {{if $praticien->_id}}
            &mdash; Dr {{$praticien}}
          {{/if}}
          &mdash; {{$date|date_format:$conf.longdate}}
        </th>
      </tr>
      <tr>
        <th class="not-printable">
          <button class="print notext not-printable me-tertiary" style="float:left;" onclick="$('mouvements_').print()">{{tr}}Print{{/tr}}</button>
          {{tr}}CMovement-incoming{{/tr}}
        </th>
        {{assign var=url value="?m=$m&tab=$tab"}}
        <th>{{mb_colonne class="CAffectation" field="_patient"   order_col=$order_col order_way=$order_way function=refreshList_mouvements}}</th>
        {{if $show_age_sexe_mvt}}
          <th class="narrow">
            <label title="Sexe">S</label>
          </th>
          <th class="narrow">
            {{mb_label class=CPatient field=_age}}
          </th>
        {{/if}}
        <th>{{mb_colonne class="CAffectation" field="_praticien" order_col=$order_col order_way=$order_way function=refreshList_mouvements}}</th>
        <th>Motif</th>
        {{if $show_hour_anesth_mvt}}
          <th>
            {{mb_colonne class="CAffectation" field="_hour" order_col=$order_col order_way=$order_way function=refreshList_mouvements}}
          </th>
          <th>
            {{mb_title class=COperation field=anesth_id}}
          </th>
        {{/if}}
        <th>{{mb_colonne class="CAffectation" field="_chambre"   order_col=$order_col order_way=$order_way function=refreshList_mouvements}}</th>
        <th>Provenance</th>
        <th>{{mb_colonne class="CAffectation" field="entree"     order_col=$order_col order_way=$order_way function=refreshList_mouvements}}</th>
      </tr>
      {{foreach from=$dep_entrants item=_dep_entrants_by_service key=key}}
        <tr>
          <th class="title text" colspan="100">
            {{if $by_secteur}}
              {{if $key}}{{$secteurs.$key}}{{else}}{{tr}}CSecteur.none{{/tr}}{{/if}}
            {{else}}
              {{$services.$key}}
            {{/if}}
          </th>
        </tr>
        {{foreach from=$_dep_entrants_by_service item=_sortie}}
          {{mb_include module=hospi template=inc_check_deplacement_line sens="entrants"}}
        {{/foreach}}
        {{foreachelse}}
        <tr>
          <td colspan="100" class="empty">{{tr}}CSejour.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    </table>
  </div>
  <div id="places-{{$type}}_sortants" style="display: none;" class="me-no-border me-no-align">
    <table class="tbl me-no-align me-no-box-shadow">
      <tr class="only-printable">
        <th class="title text" colspan="100">
          {{tr}}CMovement-incoming{{/tr}} ({{$dep_sortants|@count}})
          {{if $praticien->_id}}
            &mdash; Dr {{$praticien}}
          {{/if}}
          &mdash; {{$date|date_format:$conf.longdate}}
        </th>
      </tr>
      <tr>
        <th class="not-printable">
          <button class="print notext me-tertiary" style="float:left;" onclick="$('mouvements_').print()">{{tr}}Print{{/tr}}</button>
          {{tr}}CMovement-outgoing{{/tr}}
        </th>
        {{assign var=url value="?m=$m&tab=$tab"}}
        <th>{{mb_colonne class="CAffectation" field="_patient"   order_col=$order_col order_way=$order_way function=refreshList_mouvements}}</th>
        {{if $show_age_sexe_mvt}}
          <th class="narrow">
            <label title="Sexe">S</label>
          </th>
          <th class="narrow">{{mb_label class="CPatient" field="_age"}}</th>
        {{/if}}
        <th>{{mb_colonne class="CAffectation" field="_praticien" order_col=$order_col order_way=$order_way function=refreshList_mouvements}}</th>
        <th>Motif</th>
        {{if $show_hour_anesth_mvt}}
          <th>
            {{mb_colonne class="CAffectation" field="_hour" order_col=$order_col order_way=$order_way function=refreshList_mouvements}}
          </th>
          <th>
            {{mb_title class=COperation field=anesth_id}}
          </th>
        {{/if}}
        <th>{{mb_colonne class="CAffectation" field="_chambre"   order_col=$order_col order_way=$order_way function=refreshList_mouvements}}</th>
        <th>Destination</th>
        <th>{{mb_colonne class="CAffectation" field="sortie"     order_col=$order_col order_way=$order_way function=refreshList_mouvements}}</th>
      </tr>
      {{foreach from=$dep_sortants item=_dep_sortants_by_service key=key}}
        <tr>
          <th class="title text" colspan="100">
            {{if $by_secteur && isset($secteurs.$key|smarty:nodefaults)}}
              {{if $key}}{{$secteurs.$key}}{{else}}{{tr}}CSecteur.none{{/tr}}{{/if}}
            {{elseif !$by_secteur && isset($services.$key|smarty:nodefaults)}}
              {{$services.$key}}
            {{else}}
              Non placés
            {{/if}}
          </th>
        </tr>
        {{foreach from=$_dep_sortants_by_service item=_sortie}}
          {{mb_include module=hospi template=inc_check_deplacement_line sens="sortants"}}
        {{/foreach}}
        {{foreachelse}}
        <tr>
          <td colspan="100" class="empty">{{tr}}CSejour.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    </table>
  </div>
{{else}}
  <script>
    refreshList_{{$type}}{{$type_mouvement}} = function (order_col, order_way) {
      refreshList(order_col, order_way, '{{$type}}', '{{$type_mouvement}}');
    }
  </script>
  <ul id="tabs-edit-mouvements-{{$type}}_{{$type_mouvement}}" class="control_tabs me-margin-top-0 me-no-border-top me-no-border-right me-no-border-left">
    <li>
      <a href="#places-{{$type}}_{{$type_mouvement}}">Placés
        <small id="count_deplacements">({{$update_splitted.0}})</small>
      </a>
    </li>
    <li>
      <a href="#non-places-{{$type}}_{{$type_mouvement}}">Non placés
        <small id="count_presents">({{$update_splitted.1}})</small>
      </a>
    </li>
    {{if $type == "presents"}}
      <li>
        <a href="#desectorises-{{$type}}">Désectorisés
          <small>({{$patients_desectorises|@count}})</small>
        </a>
      </li>
      <li class="me-max-width-100">
        <div>
          <form name="chgAff" method="get">
            <input type="hidden" name="type" value="{{$type}}" />
            <input type="hidden" name="type_mouvement" value="{{$type_mouvement}}" />
            <select name="mode" onchange="$V(getForm('typeVue').mode, this.value)">
              <option value="0" {{if $mode == 0}}selected{{/if}}>{{tr}}Instant view{{/tr}}</option>
              <option value="1" {{if $mode == 1}}selected{{/if}}>{{tr}}Day view{{/tr}}</option>
            </select>
            <label>
              Heure pour vue instantanée :
              <select name="hour_instantane" onchange="$V(getForm('typeVue').hour_instantane, this.value)">
                {{foreach from=0|range:23 item=i}}
                  {{assign var=j value=$i|str_pad:2:"0":$smarty.const.STR_PAD_LEFT}}
                  <option value="{{$j}}" {{if $j == $hour_instantane}}selected{{/if}}>{{$j}}h</option>
                {{/foreach}}
              </select>
            </label>
          </form>
        </div>
      </li>
    {{/if}}
  </ul>
  <div id="places-{{$type}}_{{$type_mouvement}}" style="display: none;" class="me-no-border me-no-align">
    <table class="tbl me-no-align me-no-box-shadow">
      <tr class="only-printable">
        <th class="title text" colspan="100">
          {{if $type == "presents"}}
            Patients présents
          {{elseif $type == "ambu"}}
            {{tr}}CSejour.type.{{$type}}{{/tr}}
          {{else}}
            {{tr}}CSejour.type_mouvement.{{$type_mouvement}}{{/tr}} {{tr}}CSejour.type.{{$type}}{{/tr}}
          {{/if}}
          placé
          ({{$update_splitted.0}})
          {{if $praticien->_id}}
            &mdash; Dr {{$praticien}}
          {{/if}}
          &mdash; {{$date|date_format:$conf.longdate}}
        </th>
      </tr>
      <tr>
        {{if $show_duree_preop && $type_mouvement != "sorties"}}
          <th class="narrow">
            {{mb_colonne class="COperation" field="_heure_us" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
          </th>
        {{/if}}
        <th>
          <button class="print notext me-tertiary" style="float:left;"
                  onclick="$('places-{{$type}}_{{$type_mouvement}}').print()">{{tr}}Print{{/tr}}</button>
          {{mb_colonne class="CAffectation" field="_patient"   order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
        </th>
        {{if $show_age_sexe_mvt}}
          <th class="narrow">
            <label title="Sexe">S</label>
          </th>
          <th class="narrow">{{mb_label class="CPatient" field="_age"}}</th>
        {{/if}}
        <th>
          {{mb_colonne class="CAffectation" field="_praticien" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
        </th>
        <th>Motif</th>
        {{if $show_hour_anesth_mvt}}
          <th>
            {{mb_colonne class="CAffectation" field="_hour" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
          </th>
          <th>
            {{mb_title class=COperation field=anesth_id}}
          </th>
        {{/if}}
        {{if "dmi"|module_active}}
          <th class="narrow">{{tr}}CDM{{/tr}}</th>
        {{/if}}
        <th>
          {{mb_colonne class="CAffectation" field="_chambre"   order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
        </th>
        <th>
          {{mb_colonne class="CAffectation" field="entree"     order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
        </th>
        {{if "dPhospi mouvements print_comm_patient_present"|gconf && $type == "presents" && !$type_mouvement}}
          <th class="only-printable" style="width: 20%;">Commentaire</th>
        {{/if}}
        <th class="narrow" style="width: 5%;">
          {{mb_colonne class="CAffectation" field="sortie" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
        </th>
        {{if $type == "ambu"}}
          {{if $show_retour_mvt}}
            <th style="min-width: 100px; max-width: 100px; width: 100px;">Retour de bloc</th>
          {{/if}}
          {{if $show_collation_mvt}}
            <th style="min-width: 100px; max-width: 100px; width: 100px;">Collation</th>
          {{/if}}
          {{if $show_sortie_mvt}}
            <th style="min-width: 100px; max-width: 100px; width: 100px;">Sortie</th>
          {{/if}}
        {{/if}}
      </tr>
      {{foreach from=$mouvements item=_mouvements_by_service key=key}}
        <tr>
          <th class="title text" colspan="100">
            {{if $by_secteur && isset($secteurs.$key|smarty:nodefaults)}}
              {{if $key}}{{$secteurs.$key}}{{else}}{{tr}}CSecteur.none{{/tr}}{{/if}}
            {{elseif !$by_secteur && isset($services.$key|smarty:nodefaults)}}
              {{$services.$key}}
            {{else}}
              Non placés
            {{/if}}
          </th>
        </tr>
        {{foreach from=$_mouvements_by_service item=_affectation}}
          {{mb_include module=hospi template=inc_check_sortie_line affectation=$_affectation sejour=$_affectation->_ref_sejour}}
        {{/foreach}}
        {{foreachelse}}
        <tr>
          <td colspan="100" class="empty">{{tr}}CSejour.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    </table>
  </div>
  <div id="non-places-{{$type}}_{{$type_mouvement}}" style="display: none;" class="me-no-border me-no-align">
    <table class="tbl me-no-align me-no-box-shadow">
      <tr class="only-printable">
        <th class="title text" colspan="100">
          {{if $type == "presents"}}
            Patients présents
          {{elseif $type == "ambu"}}
            {{tr}}CSejour.type.{{$type}}{{/tr}}
          {{else}}
            {{tr}}CSejour.type_mouvement.{{$type_mouvement}}{{/tr}} {{tr}}CSejour.type.{{$type}}{{/tr}}
          {{/if}}
          non placé
          ({{$update_splitted.1}})
          {{if $praticien->_id}}
            &mdash; Dr {{$praticien}}
          {{/if}}
          &mdash; {{$date|date_format:$conf.longdate}}
        </th>
      </tr>
      <tr>
        {{if $show_duree_preop && $type_mouvement != "sorties"}}
          <th class="narrow">
            {{mb_colonne class="COperation" field="_heure_us" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
          </th>
        {{/if}}
        <th>
          <button class="print notext not-printable me-tertiary" style="float:left;"
                  onclick="$('non-places-{{$type}}_{{$type_mouvement}}').print()">{{tr}}Print{{/tr}}</button>
          {{mb_colonne class="CAffectation" field="_patient" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
        </th>
        {{if $show_age_sexe_mvt}}
          <th class="narrow">
            <label title="Sexe">S</label>
          </th>
          <th class="narrow">{{mb_label class="CPatient" field="_age"}}</th>
        {{/if}}
        <th>{{mb_colonne class="CAffectation" field="_praticien" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}</th>
        <th>Motif</th>
        {{if $show_hour_anesth_mvt}}
          <th>
            {{mb_colonne class="CAffectation" field="_hour" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
          </th>
          <th>
            {{mb_title class=COperation field=anesth_id}}
          </th>
        {{/if}}
        {{if "dmi"|module_active}}
          <th class="narrow">{{tr}}CDM{{/tr}}</th>
        {{/if}}
        <th>
          {{mb_colonne class="CAffectation" field="_chambre"   order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
        </th>
        <th>{{mb_colonne class="CAffectation" field="entree" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}</th>
        {{if "dPhospi mouvements print_comm_patient_present"|gconf && $type == "presents" && !$type_mouvement}}
          <th class="only-printable" style="width: 20%;">Commentaire</th>
        {{/if}}
        <th class="narrow"
            style="width: 5%;">{{mb_colonne class="CAffectation" field="sortie" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}</th>
        {{if $type == "ambu"}}
          {{if $show_retour_mvt}}
            <th style="min-width: 100px; max-width: 100px; width: 100px;">Retour de bloc</th>
          {{/if}}
          {{if $show_collation_mvt}}
            <th style="min-width: 100px; max-width: 100px; width: 100px;">Collation</th>
          {{/if}}
          {{if $show_sortie_mvt}}
            <th style="min-width: 100px; max-width: 100px; width: 100px;">Sortie</th>
          {{/if}}
        {{/if}}
      </tr>
      {{foreach from=$mouvementsNP item=_mouvemementsNP_by_service key=key}}
        <tr>
          <th class="title text" colspan="100">
            {{if $by_secteur && isset($secteurs.$key|smarty:nodefaults)}}
              {{if $key}}{{$secteurs.$key}}{{else}}{{tr}}CSecteur.none{{/tr}}{{/if}}
            {{elseif !$by_secteur && isset($services.$key|smarty:nodefaults)}}
              {{$services.$key}}
            {{else}}
              Non placés
            {{/if}}
          </th>
        </tr>
        {{foreach from=$_mouvemementsNP_by_service item=_sejour}}
          {{mb_include module=hospi template=inc_check_sortie_line affectation=0 sejour=$_sejour}}
        {{/foreach}}
        {{foreachelse}}
        <tr>
          <td colspan="100" class="empty">{{tr}}CSejour.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    </table>
  </div>
  {{if $type == "presents"}}
    <div id="desectorises-{{$type}}" class="me-no-border me-no-align">
      <table class="tbl me-no-align me-no-box-shadow">
        <tr>
          {{if $show_duree_preop}}
            <th class="narrow">
              {{mb_colonne class="COperation" field="_heure_us" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
            </th>
          {{/if}}
          <th>
            <button class="print notext not-printable me-tertiary" style="float:left;"
                    onclick="$('desectorises-{{$type}}').print()">{{tr}}Print{{/tr}}</button>
            {{mb_colonne class="CAffectation" field="_patient"   order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
          </th>
          {{if $show_age_sexe_mvt}}
            <th class="narrow">
              <label title="Sexe">S</label>
            </th>
            <th class="narrow">{{mb_label class="CPatient" field="_age"}}</th>
          {{/if}}
          <th>
            {{mb_colonne class="CAffectation" field="_praticien" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
          </th>
          <th>Motif</th>
          {{if $show_hour_anesth_mvt}}
            <th>
              {{mb_colonne class="CAffectation" field="_hour" order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
            </th>
            <th>
              {{mb_title class=COperation field=anesth_id}}
            </th>
          {{/if}}
          {{if "dmi"|module_active}}
            <th class="narrow">{{tr}}CDM{{/tr}}</th>
          {{/if}}
          <th>
            {{mb_colonne class="CAffectation" field="_chambre"   order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
          </th>
          <th>
            Service d'origine
          </th>
          <th>
            {{mb_colonne class="CAffectation" field="entree"     order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
          </th>
          {{if "dPhospi mouvements print_comm_patient_present"|gconf && $type == "presents" && !$type_mouvement}}
            <th class="only-printable" style="height: 20%;">Commentaire</th>
          {{/if}}
          <th class="narrow" style="width: 5%;">
            {{mb_colonne class="CAffectation" field="sortie"     order_col=$order_col order_way=$order_way function=refreshList_$type$type_mouvement}}
          </th>
        </tr>
        {{foreach from=$patients_desectorises item=_patient_desectorise}}
          {{mb_include module=hospi template=inc_check_sortie_line affectation=$_patient_desectorise desectorise=1 sejour=$_patient_desectorise->_ref_sejour}}
          {{foreachelse}}
          <tr>
            <td colspan="100" class="empty">{{tr}}CSejour.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </div>
  {{/if}}
{{/if}}