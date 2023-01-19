/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import OxCollection from "@/core/models/OxCollection"
import OxObject from "@/core/models/OxObject"

/**
 * ÒxCollection tests
 */
export default class OxCollectionTest extends OxTest {
    protected component = "OxCollection"

    public testDeleteUnknownItem () {
        const collection = new OxCollection()
        collection.objects = [
            { id: "1", type: "object" } as unknown as OxObject,
            { id: "2", type: "object" } as unknown as OxObject
        ]
        collection.meta = { count: 2, total: 2 }
        const itemToDelete = { id: "3", type: "object" } as unknown as OxObject
        collection.deleteItem(itemToDelete)
        expect(collection.objects).toEqual([
            { id: "1", type: "object" },
            { id: "2", type: "object" }
        ])
        expect(collection.total).toBe(2)
    }

    public testDeleteItem () {
        const collection = new OxCollection()
        collection.objects = [
            { id: "1", type: "object" } as unknown as OxObject,
            { id: "2", type: "object" } as unknown as OxObject
        ]
        collection.meta = { count: 2, total: 2 }
        const itemToDelete = { id: "1", type: "object" } as unknown as OxObject
        collection.deleteItem(itemToDelete)
        expect(collection.objects).toEqual([
            { id: "2", type: "object" }
        ])
        expect(collection.total).toBe(1)
    }
}

(new OxCollectionTest()).launchTests()
