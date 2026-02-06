export type TodoList = {
    id: number
    title: string
    ownerEmail?: string | null
}

export type Task = {
    id: number
    title: string
    done: boolean
    priority?: "Basse" | "Moyenne" | "Haute"
}
