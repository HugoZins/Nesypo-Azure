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
import { Button } from "@/components/ui/button"
import { useDeleteTodoList } from "@/hooks/todoLists/useDeleteTodoList"
import type { TodoList } from "@/types/todo"

interface DeleteTodoListAlertProps {
	todoList: TodoList
	onSuccess?: () => void
}

export function DeleteTodoListAlert({ todoList, onSuccess }: DeleteTodoListAlertProps) {
	const deleteTodoList = useDeleteTodoList(onSuccess)

	return (
		<AlertDialog>
			<AlertDialogTrigger asChild>
				<Button variant="destructive" size="sm">
					Supprimer
				</Button>
			</AlertDialogTrigger>

			<AlertDialogContent>
				<AlertDialogHeader>
					<AlertDialogTitle>Supprimer la TodoList ?</AlertDialogTitle>
					<AlertDialogDescription>
						Cette action est irréversible. La TodoList <strong>{todoList.title}</strong> et toutes ses tâches seront
						supprimées.
					</AlertDialogDescription>
				</AlertDialogHeader>

				<AlertDialogFooter>
					<AlertDialogCancel>Annuler</AlertDialogCancel>
					<AlertDialogAction
						onClick={() => deleteTodoList.mutate(todoList.id)}
						className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
					>
						Supprimer
					</AlertDialogAction>
				</AlertDialogFooter>
			</AlertDialogContent>
		</AlertDialog>
	)
}
