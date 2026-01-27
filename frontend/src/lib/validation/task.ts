import {z} from "zod";

export const taskSchema = z.object({
    title: z.string().min(3, "Le titre doit contenir au moins 3 caractères"),
    done: z.boolean(),
    todoListId: z.number(),
});
