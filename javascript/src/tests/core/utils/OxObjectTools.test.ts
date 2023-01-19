/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import OxObject from "@/core/models/OxObject"
import { getOxObjectsDiff, getRelationDiff } from "@/core/utils/OxObjectTools"

/**
 * OxObjectTools tests
 */
export default class OxObjectToolsTest extends OxTest {
    protected component = "OxObjectTools"

    public testGetOxObjectDiffWhenNoChange () {
        const original = new TestObject()
        original.description = "Original object"
        original.title = "Original"
        original.value = 1
        original.active = true
        const mutated = new TestObject()
        mutated.description = "Original object"
        mutated.title = "Original"
        mutated.value = 1
        mutated.active = true
        const result = getOxObjectsDiff(original, mutated)
        expect(result).toEqual({})
    }

    public testGetOxObjectDiffWhenChanges () {
        const original = new TestObject()
        original.description = "Original object"
        original.title = "Original"
        original.value = 1
        original.active = true
        const mutated = new TestObject()
        mutated.description = "Mutated object"
        mutated.title = "Original"
        mutated.value = 4
        mutated.active = true
        const result = getOxObjectsDiff(original, mutated)
        expect(result).toEqual({
            description: "Mutated object",
            value: 4
        })
    }

    public testGetOxObjectDiffWithBooleanFalse () {
        const original = new TestObject()
        original.description = "Original object"
        original.title = "Original"
        original.value = 1
        original.active = true
        const mutated = new TestObject()
        mutated.description = "Original object"
        mutated.title = "Original"
        mutated.value = 1
        mutated.active = false
        const result = getOxObjectsDiff(original, mutated)
        expect(result).toEqual({
            active: false
        })
    }

    public testGetOxObjectDiffWithBooleanTrue () {
        const original = new TestObject()
        original.description = "Original object"
        original.title = "Original"
        original.value = 1
        original.active = false
        const mutated = new TestObject()
        mutated.description = "Original object"
        mutated.title = "Original"
        mutated.value = 1
        mutated.active = true
        const result = getOxObjectsDiff(original, mutated)
        expect(result).toEqual({
            active: true
        })
    }

    public testGetOxObjectDiffWarning () {
        // @ts-ignore
        window.console.warn = jest.fn()
        const original = new TestObject()
        original.description = "Original object"
        original.title = "Original"
        original.active = true
        const mutated = new TestObject()
        mutated.description = "Original object"
        mutated.title = "Original"
        mutated.value = 1
        mutated.active = true
        getOxObjectsDiff(original, mutated)
        expect(console.warn).toBeCalled()
    }

    public testGetRelationDiffWithoutOriginalRelations () {
        const original = new TestObject()
        const mutated = new TestObject()
        mutated.relationships = {
            relation1: {
                data: [
                    {
                        type: "another_object",
                        id: "3000"
                    },
                    {
                        type: "another_object",
                        id: "3001"
                    }
                ]
            },
            relation2: {
                data: {
                    type: "another_object2",
                    id: "3"
                }
            }
        }
        const result = getRelationDiff(original, mutated)
        expect(result).toEqual({
            relation1: {
                data: [
                    {
                        type: "another_object",
                        id: "3000"
                    },
                    {
                        type: "another_object",
                        id: "3001"
                    }
                ]
            },
            relation2: {
                data: {
                    type: "another_object2",
                    id: "3"
                }
            }
        })
    }

    public testGetRelationDiffWithPartialChange () {
        const original = new TestObject()
        original.relationships = {
            relation1: {
                data: [
                    {
                        type: "another_object",
                        id: "3000"
                    },
                    {
                        type: "another_object",
                        id: "3001"
                    }
                ]
            },
            relation2: {
                data: {
                    type: "another_object2",
                    id: "3"
                }
            }
        }
        const mutated = new TestObject()
        mutated.relationships = {
            relation1: {
                data: [
                    {
                        type: "another_object",
                        id: "3000"
                    },
                    {
                        type: "another_object",
                        id: "3001"
                    },
                    {
                        type: "another_object",
                        id: "3002"
                    }
                ]
            },
            relation2: {
                data: {
                    type: "another_object2",
                    id: "3"
                }
            }
        }
        const result = getRelationDiff(original, mutated)
        expect(result).toEqual({
            relation1: {
                data: [
                    {
                        type: "another_object",
                        id: "3000"
                    },
                    {
                        type: "another_object",
                        id: "3001"
                    },
                    {
                        type: "another_object",
                        id: "3002"
                    }
                ]
            }
        })
    }

    public testGetRelationDiffWithNewRelation () {
        const original = new TestObject()
        original.relationships = {
            relation1: {
                data: {
                    type: "another_object",
                    id: "3"
                }
            }
        }
        const mutated = new TestObject()
        mutated.relationships = {
            relation1: {
                data: {
                    type: "another_object",
                    id: "3"
                }
            },
            relation2: {
                data: {
                    type: "another_object2",
                    id: "1"
                }
            }
        }
        const result = getRelationDiff(original, mutated)
        expect(result).toEqual({
            relation2: {
                data: {
                    type: "another_object2",
                    id: "1"
                }
            }
        })
    }

    public testGetRelationDiffWithRelationChange () {
        const original = new TestObject()
        original.relationships = {
            relation1: {
                data: {
                    type: "another_object",
                    id: "4"
                }
            }
        }
        const mutated = new TestObject()
        mutated.relationships = {
            relation1: {
                data: {
                    type: "another_object",
                    id: "5"
                }
            }
        }
        const result = getRelationDiff(original, mutated)
        expect(result).toEqual({
            relation1: {
                data: {
                    type: "another_object",
                    id: "5"
                }
            }
        })
    }

    public testGetRelationDiffWithNoChange () {
        const original = new TestObject()
        original.relationships = {
            relation1: {
                data: {
                    type: "another_object",
                    id: "4"
                }
            }
        }
        const mutated = new TestObject()
        mutated.relationships = {
            relation1: {
                data: {
                    type: "another_object",
                    id: "4"
                }
            }
        }
        const result = getRelationDiff(original, mutated)
        expect(result).toEqual({})
    }
}

class TestObject extends OxObject {
    constructor () {
        super()
        this.type = "test_object"
    }

    get description (): string {
        return super.get("description")
    }

    set description (value: string) {
        this.set("description", value)
    }

    get title (): string {
        return super.get("title")
    }

    set title (value: string) {
        this.set("title", value)
    }

    get value (): number {
        return super.get("value")
    }

    set value (value: number) {
        this.set("value", value)
    }

    get active (): boolean {
        return super.get("active")
    }

    set active (value: boolean) {
        this.set("active", value)
    }
}

class AnotherObject extends OxObject {
    constructor () {
        super()
        this.type = "another_object"
    }
}

(new OxObjectToolsTest()).launchTests()
