"use client"

import { CreateTodoListDialog } from "@/components/todo/CreateTodoListDialog"
import { TodoListTable } from "@/components/todo/TodoListTable"

export default function DashboardPage() {
	return (
		<div className="space-y-6">
			<div className="flex items-center justify-between">
				<h2 className="font-bold text-xl">Vos Todolists</h2>
				<CreateTodoListDialog />
			</div>

			<TodoListTable />
		</div>
	)
}
