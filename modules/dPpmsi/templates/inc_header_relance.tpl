{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<thead id="title_lines">
<tr>
    <th class="not-printable narrow"></th>
    <th class="narrow">
        {{tr}}CPatient-NDA{{/tr}}
    </th>
    <th>{{mb_colonne class=CPatient field=nom label=Patient order_col=$order_col order_way=$order_way function='Relance.changeSort'}}</th>
    <th>{{mb_colonne class=CSejour field=entree label=entree order_col=$order_col order_way=$order_way function='Relance.changeSort'}}</th>
    <th>{{mb_colonne class=CSejour field=sortie label=sortie order_col=$order_col order_way=$order_way function='Relance.changeSort'}}</th>
    <th>{{mb_colonne class=CSejour field=sortie_reelle label="Stat." order_col=$order_col order_way=$order_way function='Relance.changeSort'}}</th>
    <th>{{mb_colonne class=CRelancePMSI field=chir_id order_col=$order_col order_way=$order_way function='Relance.changeSort'}}</th>
    <th class="narrow">{{tr}}CRelancePMSI-Restate Status{{/tr}}</th>
    {{foreach from='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs" item=doc}}
        {{if "dPpmsi relances $doc"|gconf}}
            <th style="width: 46px;"
                title="{{tr}}CRelancePMSI-{{$doc}}-desc{{/tr}}">{{tr}}CRelancePMSI-{{$doc}}-court{{/tr}}</th>
        {{/if}}
    {{/foreach}}
    <th>{{tr}}CRelancePMSI-commentaire_dim{{/tr}}</th>
    <th>{{tr}}CRelancePMSI-Medical Comment-court{{/tr}}</th>
    <th>{{mb_colonne class=CRelancePMSI field=urgence order_col=$order_col order_way=$order_way function='Relance.changeSort'}}</th>
    <th>{{tr}}common-Uread{{/tr}}</th>
</tr>
</thead>
