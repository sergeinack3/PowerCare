{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr class="clear">
  <th colspan="{{$_materiel+$_extra+$_duree+$_coordonnees+12}}">
    {{mb_include module=bloc template=inc_offline_button_print_view_planning}}

    <h1 style="margin: auto;">
      <a href="#" onclick="window.print();">
        Planning du {{$filter->_datetime_min|date_format:$conf.date}} {{$filter->_datetime_min|date_format:$conf.time}}
        au {{$filter->_datetime_max|date_format:$conf.date}} {{$filter->_datetime_max|date_format:$conf.time}}
        -
        {{$numOp}} intervention(s)
        {{if $operations|@count && $_hors_plage}}
          (dont {{$operations|@count}} hors plage)
        {{/if}}
      </a>
    </h1>
  </th>
</tr>