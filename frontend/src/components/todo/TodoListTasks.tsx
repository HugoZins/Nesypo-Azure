"use client"

import { useRouter } from "next/navigation"
import { CreateTaskDialog } from "@/components/todo/CreateTaskDialog"
import { DeleteTaskAlert } from "@/components/todo/DeleteTaskAlert"
import { DeleteTodoListAlert } from "@/components/todo/DeleteTodoListAlert"
import { EditTaskDialog } from "@/components/todo/EditTaskDialog"
import { EditTodoListDialog } from "@/components/todo/EditTodoListDialog"
import { TodoListTasksSkeleton } from "@/components/todo/TodoListTasksSkeleton"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Checkbox } from "@/components/ui/checkbox"
import { Progress } from "@/components/ui/progress"
import { Separator } from "@/components/ui/separator"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { useTasks } from "@/hooks/tasks/useTasks"
import { useUpdateTask } from "@/hooks/tasks/useUpdateTask"
import { getProgressColor } from "@/lib/utils"
import type { TodoList } from "@/types/todo"

export function TodoListTasks({ todoList }: { todoList: TodoList }) {
	const router = useRouter()
	const { data: tasks = [], isLoading } = useTasks(todoList.id)
	const updateTask = useUpdateTask(todoList.id)

	if (isLoading) {
		return <TodoListTasksSkeleton />
	}

	const completed = tasks.filter((task) => task.done).length
	const total = tasks.length
	const progress = total === 0 ? 0 : Math.round((completed / total) * 100)

	return (
		<div className="space-y-6">
			{/* HEADER */}
			<Card>
				<CardHeader>
					<CardTitle className="flex items-center justify-between">
						<span>{todoList.title}</span>
						<div className="flex items-center gap-2">
							<EditTodoListDialog todoList={todoList} />
							<DeleteTodoListAlert todoList={todoList} onSuccess={() => router.push("/dashboard")} />
						</div>
					</CardTitle>

					<div className="mt-2">
						<Progress value={progress} indicatorClassName={getProgressColor(progress)} />
						<div className="mt-1 text-muted-foreground text-sm">
							{completed}/{total} tâches terminées
						</div>
					</div>
				</CardHeader>
			</Card>

			<Separator />

			{/* TASKS */}
			<Card>
				<CardHeader className="flex flex-row items-center justify-between">
					<CardTitle>Tâches</CardTitle>
					<CreateTaskDialog todoListId={todoList.id} />
				</CardHeader>

				<CardContent>
					{tasks.length === 0 ? (
						<div className="py-6 text-center text-muted-foreground text-sm">Aucune tâche pour cette todolist</div>
					) : (
						<Table>
							<TableHeader>
								<TableRow>
									<TableHead>Nom</TableHead>
									<TableHead>Statut</TableHead>
									<TableHead>Priorité</TableHead>
									<TableHead>Actions</TableHead>
								</TableRow>
							</TableHeader>

							<TableBody>
								{tasks.map((task) => (
									<TableRow key={task.id}>
										<TableCell>
											<button
												type="button"
												className="flex w-full cursor-pointer select-none items-center gap-2 border-0 bg-transparent p-0 text-left"
												onClick={() =>
													updateTask.mutate({
														id: task.id,
														data: { done: !task.done },
													})
												}
											>
												<Checkbox
													checked={task.done}
													onCheckedChange={(checked) =>
														updateTask.mutate({
															id: task.id,
															data: { done: checked === true },
														})
													}
													onClick={(e) => e.stopPropagation()}
												/>
												<span className={task.done ? "text-muted-foreground line-through" : ""}>{task.title}</span>
											</button>
										</TableCell>

										<TableCell>{task.done ? "✔️ Fait" : "⏳ À faire"}</TableCell>

										<TableCell>{task.priority ?? "—"}</TableCell>

										<TableCell>
											<div className="flex items-center gap-2">
												<EditTaskDialog task={task} todoListId={todoList.id} />
												<DeleteTaskAlert task={task} todoListId={todoList.id} />
											</div>
										</TableCell>
									</TableRow>
								))}
							</TableBody>
						</Table>
					)}
				</CardContent>
			</Card>
		</div>
	)
}
