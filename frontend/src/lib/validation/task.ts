import { z } from "zod"
import type { TaskPriority } from "@/types/todo"

export const TASK_PRIORITIES = ["Basse", "Moyenne", "Haute"] as const satisfies readonly TaskPriority[]

export const taskSchema = z.object({
	title: z.string().min(3, "Le titre doit contenir au moins 3 caractères"),
	todoListId: z.number(),
	priority: z.enum(TASK_PRIORITIES, {
		error: () => ({ message: "Veuillez sélectionner une priorité" }),
	}),
})
