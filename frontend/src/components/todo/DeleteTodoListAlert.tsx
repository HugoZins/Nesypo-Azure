"use client"

import {
	AlertDialog,
	AlertDialogAction,
	AlertDialogCancel,
	AlertDialogContent,
	AlertDialogDescription,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogTitle,
	AlertDialogTrigger,
} from "@/components/ui/alert-dialog"
import { useDeleteTodoList } from "@/hooks/todoLists/useDeleteTodoList"
import type { TodoList } from "@/types/todo"

interface DeleteTodoListAlertProps {
	todoList: TodoList
}

export function DeleteTodoListAlert({ todoList }: DeleteTodoListAlertProps) {
	const deleteTodoList = useDeleteTodoList()

	return (
		<AlertDialog>
			<AlertDialogTrigger asChild>
				<button type="button" className="rounded bg-red-600 px-3 py-1 text-sm text-white">Supprimer</button>
			</AlertDialogTrigger>

			<AlertDialogContent>
				<AlertDialogHeader>
					<AlertDialogTitle>Supprimer la TodoList ?</AlertDialogTitle>
					<AlertDialogDescription>
						Cette action est irréversible. La TodoList <strong>{todoList.title}</strong> sera supprimée.
					</AlertDialogDescription>
				</AlertDialogHeader>

				<AlertDialogFooter>
					<AlertDialogCancel>Annuler</AlertDialogCancel>
					<AlertDialogAction onClick={() => deleteTodoList.mutate(todoList.id)} className="bg-red-600 text-white">
						Supprimer
					</AlertDialogAction>
				</AlertDialogFooter>
			</AlertDialogContent>
		</AlertDialog>
	)
}
