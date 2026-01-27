"use client"

import {ColumnDef} from "@tanstack/react-table"
import {TodoList} from "@/types/todo"
import {Progress} from "@/components/ui/progress"
import Link from "next/link"
import {Button} from "@/components/ui/button"
import {DeleteTodoListAlert} from "@/components/todo/DeleteTodoListAlert"

export const columns: ColumnDef<TodoList>[] = [
    {
        accessorKey: "title",
        header: "Nom",
        cell: ({row}) => (
            <span className="font-medium">{row.original.title}</span>
        ),
    },
    {
        id: "progress",
        header: "Progression",
        cell: ({row}) => {
            const progress = row.original.progress ?? 0

            return (
                <div className="flex items-center gap-2">
                    <Progress value={progress} className="w-32"/>
                    <span className="text-sm text-muted-foreground">{progress}%</span>
                </div>
            )
        },
    },
    {
        id: "actions",
        header: "Actions",
        cell: ({row}) => (
            <div className="flex gap-2">
                {/* Bouton Voir */}
                <Link href={`/dashboard/todo-lists/${row.original.id}`}>
                    <Button variant="link" size="sm">
                        Voir
                    </Button>
                </Link>

                {/* Bouton Supprimer */}
                <DeleteTodoListAlert todoList={row.original}/>
            </div>
        ),
    },
]
