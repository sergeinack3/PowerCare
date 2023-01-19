/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import OxObject from "@/core/models/OxObject"
import { setActivePinia } from "pinia"
import pinia from "@/core/plugins/OxPiniaCore"
import * as OxStorage from "@/core/utils/OxStorage"
import { useIncludedStore } from "@/core/stores/included"

/**
 * OxObject tests
 */
export default class OxObjectTest extends OxTest {
    protected component = "OxObject"
    private includedStore

    protected beforeAllTests () {
        super.beforeAllTests()
        setActivePinia(pinia)
        this.includedStore = useIncludedStore()
    }

    protected afterTest () {
        super.afterTest()
        this.includedStore.objects = []
    }

    public testResetUnknownForwardRelation () {
        const object = new OxObject()
        object.setForwardRelation("relation", null)
        expect(object.relationships).toEqual({
            relation: {
                data: null
            }
        })
    }

    public testResetExistingForwardRelation () {
        const object = new OxObject()
        object.setForwardRelation("relation", { id: "1", type: "object" } as unknown as OxObject)
        object.setForwardRelation("relation", null)
        expect(object.relationships.relation).toEqual({
            data: null
        })
    }

    public testSetNewForwardRelation () {
        const object = new OxObject()
        const spyStoreSchema = jest.spyOn(OxStorage, "storeObject")
        const objectRelation = { id: "1", type: "object" } as unknown as OxObject
        object.setForwardRelation("relation", objectRelation)
        expect(object.relationships.relation).toEqual({
            data: objectRelation
        })
        expect(spyStoreSchema).toBeCalledWith(objectRelation)
    }

    public testUpdateForwardRelation () {
        const object = new OxObject()
        object.setForwardRelation("relation", { id: "1", type: "object" } as unknown as OxObject)
        object.setForwardRelation("relation", { id: "2", type: "object" } as unknown as OxObject)
        expect(object.relationships.relation).toEqual({
            data: {
                type: "object",
                id: "2"
            }
        })
    }

    public testLoadForwardRelationWhenEmpty () {
        const object = new OxObject()
        expect(object.loadForwardRelation("relation")).toBeNull()
    }

    public testLoadNonExistingForwardRelation () {
        const object = new OxObject()
        object.setForwardRelation("relation", { id: "1", type: "object" } as unknown as OxObject)
        expect(object.loadForwardRelation("unknownRelation")).toBeNull()
    }

    public testLoadForwardOnMultipleRelation () {
        const object = new OxObject()
        const relationObject = new OxObject()
        relationObject.attributes.value = true
        relationObject.attributes.number = 8
        object.addBackwardRelation("relation", relationObject)
        expect(() => object.loadForwardRelation("relation")).toThrowError("Multiple forward relations found")
    }

    public testLoadForwardRelationWithoutId () {
        const object = new OxObject()
        const relationObject = new OxObject()
        object.setForwardRelation("relation", object)
        expect(() => object.loadForwardRelation("relation")).toThrowError("Id relations not found")
    }

    public testLoadForwardRelation () {
        const object = new OxObject()
        const relationObject = new OxObject()
        relationObject.id = "1"
        relationObject.attributes = { value: false, label: "test" }
        object.setForwardRelation("relation", relationObject)
        expect(object.loadForwardRelation("relation")).toEqual(relationObject)
    }

    public testAddBackwardRelation () {
        const object = new OxObject()
        object.addBackwardRelation("relation", { id: "1", type: "object" } as unknown as OxObject)
        object.addBackwardRelation("relation", { id: "2", type: "object" } as unknown as OxObject)
        expect(object.relationships.relation).toEqual({
            data: [
                { id: "1", type: "object" },
                { id: "2", type: "object" }
            ]
        })
    }

    public testLoadBackwardRelationWhenEmpty () {
        const object = new OxObject()
        expect(object.loadBackwardRelation("relation")).toEqual([])
    }

    public testLoadBackwardOnSingleRelation () {
        const object = new OxObject()
        const relationObject = new OxObject()
        relationObject.attributes.value = true
        relationObject.attributes.number = 8
        object.setForwardRelation("relation", relationObject)
        expect(() => object.loadBackwardRelation("relation")).toThrowError("Backward relation is not an array")
    }

    public testLoadBackwardRelation () {
        const object = new OxObject()
        const relationObject1 = new OxObject()
        relationObject1.id = "1"
        relationObject1.attributes = { value: false, label: "test" }
        const relationObject2 = new OxObject()
        relationObject2.id = "2"
        relationObject2.attributes = { value: false, label: "test" }
        object.addBackwardRelation("relation", relationObject1)
        object.addBackwardRelation("relation", relationObject2)
        expect(object.loadBackwardRelation("relation")).toEqual([
            relationObject1,
            relationObject2
        ])
    }
}

(new OxObjectTest()).launchTests()
