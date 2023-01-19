{{*
 * @package Mediboard\Ressources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Calendar.regField(getForm("addPlage").date);
    Calendar.regField(getForm("changeDate").debut, null, {noView: true});
  });
</script>

<table class="main">
  <tr>
    <th class="title" colspan="2">
      <a href="?m={{$m}}&debut={{$prec}}">&lt;&lt;&lt;</a>
      Semaine du {{$debut|date_format:$conf.longdate}}
      <form name="changeDate" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <input type="hidden" name="debut" class="date" value="{{$debut}}" onchange="this.form.submit()" />
      </form>
      <a href="?m={{$m}}&debut={{$suiv}}">&gt;&gt;&gt;</a>
    </th>
  </tr>
  <tr>
    <td>
      <table width="100%" id="weeklyPlanning">
        <tr>
          <th></th>
          {{foreach from=$plages key=_day item=plagesPerDay}}
            <th scope="col" style="width: {{math equation="x/y" x=100 y=$plages|@count}}%">{{$_day|date_format:"%A %d"}}</th>
          {{/foreach}}
        </tr>
        {{foreach from=$listHours item=_hour}}
          <tr>
            <th scope="row">{{$_hour}}h</th>
            {{foreach from=$plages key=_day item=plagesPerDay}}
              {{assign var="isNotIn" value=1}}
              {{foreach from=$plagesPerDay item=_plage}}
                {{if $_plage->_hour_deb == $_hour}}
                  <td align="center" bgcolor="{{$_plage->_state}}" rowspan="{{$_plage->_hour_fin-$_plage->_hour_deb}}">
                    <a href="?m={{$m}}&tab={{$tab}}&plage_id={{$_plage->_id}}">
                      {{if $_plage->libelle}}
                        {{$_plage->libelle}}
                        <br />
                      {{/if}}
                      {{$_plage->tarif|currency}}
                      <br />
                      {{$_plage->_hour_deb}}h - {{$_plage->_hour_fin}}h
                      {{if $_plage->prat_id}}
                        <br />
                        {{$_plage->_ref_prat->_view}}
                      {{/if}}
                    </a>
                  </td>
                {{/if}}
                {{if ($_plage->_hour_deb <= $_hour) && ($_plage->_hour_fin > $_hour)}}
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
      <a class="button new" href="?m={{$m}}&plage_id=0">{{tr}}CPlageressource-title-create{{/tr}}</a>
      <form name="addPlage" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">

        <input type='hidden' name='dosql' value='do_plageressource_aed' />
        {{mb_key object=$plage}}

        <table class="form">
          {{mb_include module=system template=inc_form_table_header object=$plage colspan=4}}
          <tr>
            <th>{{mb_label object=$plage field="date"}}</th>
            <td>{{mb_field object=$plage field="date" register=true form="addPlage"}}
            <th>{{mb_label object=$plage field="debut"}}</th>
            <td>{{mb_field object=$plage field="debut" register=true form="addPlage"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$plage field="libelle"}}</th>
            <td>{{mb_field object=$plage field="libelle"}}</td>
            <th>{{mb_label object=$plage field="fin"}}</th>
            <td>{{mb_field object=$plage field="fin" register=true form="addPlage"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$plage field="tarif"}}</th>
            <td>{{mb_field object=$plage field="tarif"}}</td>
            <th><label for="_repeat" title="Nombre de semaine concernées">Répétition:</label></th>
            <td><input type="text" name="_repeat" size="3" value="1" /></td>
          </tr>
          <tr>
            <th>{{mb_label object=$plage field="prat_id"}}</th>
            <td>
              <select name="prat_id" style="width: 20em;">
                {{mb_include module=mediusers template=inc_options_mediuser list=$listPrat selected=$plage->prat_id}}
              </select>
            </td>
            <th><label for="_double" title="Cochez pour n'affecter qu'une semaine sur deux">1 sem / 2</label></th>
            <td><input type="checkbox" name="_double" /></td>
          </tr>
          <tr>
            <td class="button" colspan="4">
              {{if $plage->_id}}
                <button class="modify" type="submit">{{tr}}Modify{{/tr}}</button>
              {{else}}
                <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
              {{/if}}
          </tr>
        </table>
      
      </form>
      
      {{if $plage->_id}}
        <hr />
        <form name="delPlage" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
          <table class="form">
            <tr>
              <th colspan="2" class="category">Supprimer cette plage</th>
            </tr>
            <tr>
              <th><label for="_repeat" title="Nombre de semaine concernées">Répétition</label></th>
              <td><input type="text" name="_repeat" size="3" value="1" /></td>
            </tr>
            <tr>
              <td class="button" colspan="2">
                <input type='hidden' name='dosql' value='do_plageressource_aed' />
                {{mb_key object=$plage}}
                <button class="trash" type="submit">Supprimer</button>
            </tr>
          </table>
        </form>
      {{/if}}
    </td>
  </tr>
</table>