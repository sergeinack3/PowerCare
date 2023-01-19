{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        if (navigator.appVersion.indexOf("Chrome/") === -1) {
            var div_sorties = $("allSorties");
            if (div_sorties && div_sorties.clientHeight < div_sorties.scrollHeight) {
                div_sorties.style.paddingRight = '16px';
            }
        }
    });
</script>

<table class="tbl me-small-tbl" style="text-align: center;">
    <tbody>
    {{foreach from=$days key=day item=counts}}
        <tr {{if $day == $date}} class="selected" {{/if}}>
            {{assign var=day_number value=$day|date_format:"%w"}}

            <td style="text-align: right;
            {{if array_key_exists($day, $bank_holidays)}}
              background-color: #fc0;
            {{elseif $day_number == '0' || $day_number == '6'}}
              background-color: #ccc;
            {{/if}}">
                <a href="#1" onclick="reloadSortiesDate(this, '{{$day|iso_date}}')"
                   title="{{$day|date_format:$conf.longdate}}">
                    <strong>
                        {{$day|date_format:"%a"|upper|substr:0:1}}
                        {{$day|date_format:"%d"}}
                    </strong>
                </a>
            </td>

            <td {{if $selSortis=='0' && $day == $date}}style="font-weight: bold;"{{/if}}>
                {{if $counts.sorties}}{{$counts.sorties}}{{else}}-{{/if}}
            </td>

            <td {{if $selSortis =='np' && $day == $date}}style="font-weight: bold;"{{/if}}>
                {{if $counts.sorties_non_preparees}}{{$counts.sorties_non_preparees}}{{else}}-{{/if}}
            </td>

            <td {{if $selSortis=='n' && $day == $date}}style="font-weight: bold;"{{/if}}>
                {{if $counts.sorties_non_effectuees}}{{$counts.sorties_non_effectuees}}{{else}}-{{/if}}
            </td>
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="10" class="empty">Pas d'admission ce mois-ci</td>
        </tr>
    {{/foreach}}

    <tr>
        <td><strong>Total</strong></td>
        <td><strong>{{$totaux.sorties|smarty:nodefaults}}</strong></td>
        <td><strong>{{$totaux.sorties_non_preparees|smarty:nodefaults}}</strong></td>
        <td><strong>{{$totaux.sorties_non_effectuees|smarty:nodefaults}}</strong></td>
    </tr>
    </tbody>
    <thead>
    <tr>
        <th class="title" colspan="4">
            <a style="display: inline;" href="#1"
               onclick="$V(getForm('selType').date, '{{$lastmonth}}'); reloadFullSorties()">&lt;&lt;&lt;</a>
            {{$date|date_format:"%b %Y"}}
            <a style="display: inline;" href="#1"
               onclick="$V(getForm('selType').date, '{{$nextmonth}}'); reloadFullSorties()">&gt;&gt;&gt;</a>
        </th>
    </tr>
    <tr>
        <th class="text">
            Date
        </th>
        <th class="text">
            <a class="{{if $selSortis=='0'}}selected{{else}}selectable{{/if}}" title="Toutes les sorties" href="#1"
               onclick="filterAdm(0)">
                Sorties
            </a>
        </th>
        <th class="text">
            <a class="{{if $selSortis == 'np'}}selected{{else}}selectable{{/if}}"
               title="{{tr}}admissions-Unprepared discharges{{/tr}}" href="#" onclick="filterAdm('np')">
                {{tr}}admissions-Unprepared discharges-court{{/tr}}
            </a>
        </th>
        <th class="text">
            <a class="{{if $selSortis=='n'}}selected{{else}}selectable{{/if}}" title="Sorties non effectuées"
               href="#1" onclick="filterAdm('n')">
                Non Eff.
            </a>
        </th>
    </tr>
    </thead>
</table>
