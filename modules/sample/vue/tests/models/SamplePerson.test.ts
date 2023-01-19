/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import SamplePerson from "@modules/sample/vue/models/SamplePerson"

/**
 * SamplePerson tests
 */
export default class SamplePersonTest extends OxTest {
    protected component = "SamplePerson"

    public testPersonFullName () {
        const person = new SamplePerson()
        person.firstName = "John"
        person.lastName = "Doe"
        expect(person.fullName).toEqual("John Doe")
    }

    public testPersonIsDirectorString () {
        const person = new SamplePerson()
        person.isDirector = true
        expect(person.isDirectorString).toEqual("CSamplePerson.is_director.y")
    }

    public testPersonIsNotDirectorString () {
        const person = new SamplePerson()
        person.isDirector = false
        expect(person.isDirectorString).toEqual("CSamplePerson.is_director.n")
    }

    public testPersonEmptyBirthdate () {
        const person = new SamplePerson()
        expect(person.activityStart).toEqual("")
    }

    public testPersonNotEmptyBirthdate () {
        const person = new SamplePerson()
        person.birthdateData = "1991-07-14"
        expect(person.birthdate).toEqual("14/07/1991")
    }

    public testPersonUndefinedSexIcon () {
        const person = new SamplePerson()
        expect(person.sexIcon).toBeUndefined()
    }

    public testPersonSexIconMale () {
        const person = new SamplePerson()
        person.sex = "m"
        expect(person.sexIcon).toEqual("male")
    }

    public testPersonSexIconFemale () {
        const person = new SamplePerson()
        person.sex = "f"
        expect(person.sexIcon).toEqual("female")
    }

    public testPersonEmptyActivityStart () {
        const person = new SamplePerson()
        expect(person.activityStart).toEqual("")
    }

    public testPersonNotEmptyActivityStart () {
        const person = new SamplePerson()
        person.activityStartData = "2021-07-14"
        expect(person.activityStart).toEqual(2021)
    }
}

(new SamplePersonTest()).launchTests()
