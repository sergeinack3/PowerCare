{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=default value=foo}}

<div id="mb-default">{{$default}}</div>

<div id="first-ditto">{{mb_ditto name=ditto value=ditto_value}}</div>
<div id="second-ditto">{{mb_ditto name=ditto value=ditto_value center=true}}</div>
<div id="third-ditto">{{mb_ditto name=ditto2 value=ditto_value}}</div>

<div id="mb-class">{{mb_class class=class_test}}</div>
<div id="mb-class-object">{{mb_class object=$object}}</div>

<div id="mb-path">{{mb_path url=foo/bar}}</div>

<div id="empty-tr">{{tr}}{{/tr}}</div>
<div id="tr">{{tr}}AND{{/tr}}</div>
<div id="tr-with-var">{{tr var1=10 var2=5 escape=JSAttribute}}Browser-error-content-chrome{{/tr}}</div>
<div id="tr-markdown">{{tr markdown=true}}This **trad** does not exists{{/tr}}</div>

<div id="emphasize-empty">{{'text'|emphasize:''}}</div>
<div id="emphasize">{{'foo bar is a barbar'|emphasize:'bar is'}}</div>

<div id="mb-script">{{mb_script module=foo script=bar}}</div>

<div id="mb-include-not-exists">{{mb_include template=foo ignore_errors=true}}</div>

<div id="thumblink">{{thumblink document_id=1 document_class=CFile class="button print" download_raw=1 page=5}}This is a link{{/thumblink}}<div>

<div id="thumbnail">{{thumbnail document_id=1 document_class=CFile profile=large default_size=1 quality=low page=2 crop=true rotate=180}}</div>

{{mb_return}}
<div id="mb-return"></div>
