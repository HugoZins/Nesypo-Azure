"use client";

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
} from "@/components/ui/alert-dialog";

import {useDeleteTask} from "@/hooks/tasks/useDeleteTask";
import {Task} from "@/types/todo";

interface DeleteTaskAlertProps {
    task: Task;
    todoListId: number;
}

export function DeleteTaskAlert({task, todoListId}: DeleteTaskAlertProps) {
    const deleteTask = useDeleteTask(todoListId);

    return (
        <AlertDialog>
            <AlertDialogTrigger asChild>
                <button className="bg-red-600 text-white px-3 py-1 rounded text-sm">
                    Supprimer
                </button>
            </AlertDialogTrigger>

            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Supprimer la tâche ?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Cette action est irréversible. La tâche{" "}
                        <strong>{task.title}</strong> sera définitivement supprimée.
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel>Annuler</AlertDialogCancel>

                    <AlertDialogAction
                        onClick={() => deleteTask.mutate(task.id)}
                        className="bg-red-600 text-white"
                    >
                        Supprimer
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
