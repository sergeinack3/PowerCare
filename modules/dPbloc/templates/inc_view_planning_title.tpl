{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="title" {{if $show_duree_preop}}colspan="2"{{/if}}></th>
  {{assign var=suffixe value="_title"}}
  {{mb_include module=bloc template=inc_planning/$col1$suffixe}}
  {{mb_include module=bloc template=inc_planning/$col2$suffixe}}
  {{mb_include module=bloc template=inc_planning/$col3$suffixe}}
  {{if $offline}}<th class="title narrow not-printable"></th>{{/if}}
</tr>
<tr>
  {{if $show_duree_preop}}<th>Heure US</th>{{/if}}
  <th>Heure</th>
  {{assign var=suffixe value="_header"}}
  {{mb_include module=bloc template=inc_planning/$col1$suffixe}}
  {{mb_include module=bloc template=inc_planning/$col2$suffixe}}
  {{mb_include module=bloc template=inc_planning/$col3$suffixe}}
  {{if $offline}}<th class="narrow not-printable"></th>{{/if}}
</tr>