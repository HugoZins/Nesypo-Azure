"use client"

import type { ColumnDef } from "@tanstack/react-table"
import Link from "next/link"
import { DeleteTodoListAlert } from "@/components/todo/DeleteTodoListAlert"
import { Button } from "@/components/ui/button"
import { Progress } from "@/components/ui/progress"
import { getProgressColor } from "@/lib/utils"
import type { TodoList } from "@/types/todo"

export function getColumns(showOwner: boolean): ColumnDef<TodoList>[] {
	const columns: ColumnDef<TodoList>[] = [
		{
			accessorKey: "title",
			header: "Nom",
			cell: ({ row }) => <span className="font-medium">{row.original.title}</span>,
		},
	]

	if (showOwner) {
		columns.push({
			accessorKey: "ownerEmail",
			header: "Propriétaire",
			cell: ({ row }) => <span className="text-sm text-muted-foreground">{row.original.ownerEmail ?? "—"}</span>,
		})
	}

	columns.push(
		{
			id: "progress",
			header: "Progression",
			cell: ({ row }) => {
				const progress = row.original.progress ?? 0
				return (
					<div className="flex items-center gap-2">
						<Progress value={progress} className="w-32" indicatorClassName={getProgressColor(progress)} />
						<span className="text-muted-foreground text-sm">{progress}%</span>
					</div>
				)
			},
		},
		{
			id: "tasks",
			header: "Tâches terminées",
			cell: ({ row }) => {
				const completed = row.original.completedTasks ?? 0
				const total = row.original.totalTasks ?? 0
				return (
					<span className="text-sm text-muted-foreground">
						{completed}/{total}
					</span>
				)
			},
		},
		{
			id: "actions",
			header: "Actions",
			cell: ({ row }) => (
				<div className="flex gap-2">
					<Link href={`/dashboard/todo-lists/${row.original.id}`}>
						<Button variant="link" size="sm">
							Voir
						</Button>
					</Link>
					<DeleteTodoListAlert todoList={row.original} />
				</div>
			),
		},
	)

	return columns
}
