import { describe, expect, it } from "vitest"
import { getProgressColor } from "@/lib/utils"

describe("getProgressColor", () => {
	it("retourne rouge pour 0%", () => {
		expect(getProgressColor(0)).toBe("bg-red-500")
	})

	it("retourne rouge pour 33%", () => {
		expect(getProgressColor(33)).toBe("bg-red-500")
	})

	it("retourne orange pour 34%", () => {
		expect(getProgressColor(34)).toBe("bg-amber-500")
	})

	it("retourne orange pour 66%", () => {
		expect(getProgressColor(66)).toBe("bg-amber-500")
	})

	it("retourne bleu pour 67%", () => {
		expect(getProgressColor(67)).toBe("bg-blue-500")
	})

	it("retourne bleu pour 99%", () => {
		expect(getProgressColor(99)).toBe("bg-blue-500")
	})

	it("retourne vert pour 100%", () => {
		expect(getProgressColor(100)).toBe("bg-green-500")
	})
})
