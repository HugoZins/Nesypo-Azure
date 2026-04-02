import { QueryClientProvider } from "@tanstack/react-query"
import { render, screen, waitFor } from "@testing-library/react"
import { describe, expect, it, vi } from "vitest"
import { TodoListTable } from "@/components/todo/TodoListTable"
import { createTestQueryClient } from "../utils"

vi.mock("next/navigation", () => ({
    useRouter: () => ({ push: vi.fn() }),
}))

vi.mock("next/link", () => ({
    default: ({ children, href }: { children: React.ReactNode; href: string }) =>
        <a href={href}>{children}</a>,
}))

vi.mock("@/hooks/auth/useMe", () => ({
    useMe: () => ({ data: { id: 1, email: "test@mail.com", roles: ["ROLE_USER"] } }),
}))

vi.mock("@/components/todo/columns", () => ({
    getColumns: () => [
        {
            id: "title",
            header: "Titre",
            cell: ({ row }: { row: { original: { title: string } } }) =>
                <span>{row.original.title}</span>,
        },
        {
            id: "progress",
            header: "Progression",
            cell: ({ row }: { row: { original: { progress: number } } }) =>
                <span>{row.original.progress}%</span>,
        },
        {
            id: "tasks",
            header: "Tâches terminées",
            cell: ({ row }: { row: { original: { completedTasks: number; totalTasks: number } } }) =>
                <span>{row.original.completedTasks}/{row.original.totalTasks}</span>,
        },
    ],
}))

function renderTodoListTable() {
    return render(
        <QueryClientProvider client={createTestQueryClient()}>
            <TodoListTable />
        </QueryClientProvider>
    )
}

describe("TodoListTable", () => {
    it("affiche un skeleton pendant le chargement", () => {
        renderTodoListTable()
        expect(document.querySelector(".animate-pulse")).toBeInTheDocument()
    })

    it("affiche les todolists après chargement", async () => {
        renderTodoListTable()

        await waitFor(() => {
            expect(screen.getByText("Liste 1")).toBeInTheDocument()
            expect(screen.getByText("Liste 2")).toBeInTheDocument()
        })
    })

    it("affiche la colonne Progression", async () => {
        renderTodoListTable()

        await waitFor(() => {
            expect(screen.getByText(/progression/i)).toBeInTheDocument()
        })
    })

    it("affiche la colonne Tâches terminées", async () => {
        renderTodoListTable()

        await waitFor(() => {
            expect(screen.getByText(/tâches terminées/i)).toBeInTheDocument()
        })
    })

    it("affiche le ratio de tâches terminées pour chaque liste", async () => {
        renderTodoListTable()

        await waitFor(() => {
            expect(screen.getByText("1/2")).toBeInTheDocument()
            expect(screen.getByText("0/3")).toBeInTheDocument()
        })
    })
})