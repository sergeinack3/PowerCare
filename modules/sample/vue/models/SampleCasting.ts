/**
 * @package Openxtrem\Sample
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
import OxObject from "@/core/models/OxObject"
import SamplePerson from "./SamplePerson"
import { OxAttr } from "@/core/types/OxObjectTypes"

export default class SampleCasting extends OxObject {
    constructor () {
        super()
        this.type = "sample_casting"
    }

    protected _relationsTypes = {
        sample_person: SamplePerson
    }

    get mainActor (): OxAttr<boolean> {
        return this.attributes.is_main_actor
    }

    set mainActor (value: OxAttr<boolean>) {
        this.set("is_main_actor", value)
    }

    get actor (): SamplePerson {
        return this.loadForwardRelation<SamplePerson>("actor") as SamplePerson
    }

    set actor (value: SamplePerson | null) {
        this.setForwardRelation<SamplePerson>("actor", value)
    }
}
