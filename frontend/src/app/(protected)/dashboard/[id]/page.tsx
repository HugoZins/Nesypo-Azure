"use client";

import { useParams } from "next/navigation";
import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

import { Progress } from "@/components/ui/progress";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Spinner } from "@/components/ui/spinner";

type Task = {
    id: number;
    title: string;
    done: boolean;
    priority?: string;
    todoList: {
        id: number;
    };
};

type TodoList = {
    id: number;
    title: string;
};

export default function TodoListPage() {
    const { id } = useParams();

    // 1) On récupère les tâches
    const {
        data: tasks,
        isLoading,
        isError,
    } = useQuery<Task[]>({
        queryKey: ["tasks", id],
        queryFn: () => api.get("api/tasks").json<Task[]>(),
        enabled: !!id,
    });

    if (isLoading) {
        return (
            <div className="flex justify-center items-center h-64">
                <Spinner />
            </div>
        );
    }

    if (isError || !tasks) {
        return <div>Erreur lors du chargement des tâches</div>;
    }

    // 2) Filtrer les tâches de la liste sélectionnée
    const tasksForList = tasks.filter((t) => t.todoList.id === Number(id));

    const completed = tasksForList.filter((t) => t.done).length;
    const total = tasksForList.length;
    const progress = total === 0 ? 0 : Math.round((completed / total) * 100);

    return (
        <div className="space-y-6">
            {/* HEADER */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex justify-between items-center">
                        <span>TodoList #{id}</span>
                        <Button>Modifier</Button>
                    </CardTitle>

                    <div className="mt-2">
                        <Progress value={progress} />
                        <div className="text-sm text-muted-foreground mt-1">
                            {completed}/{total} tâches terminées
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <Separator />

            {/* TASKS TABLE */}
            <Card>
                <CardHeader>
                    <CardTitle>Tâches</CardTitle>
                </CardHeader>

                <CardContent>
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
                            {tasksForList.map((task) => (
                                <TableRow key={task.id}>
                                    <TableCell>{task.title}</TableCell>
                                    <TableCell>
                                        {task.done ? "✔️ Fait" : "⏳ À faire"}
                                    </TableCell>
                                    <TableCell>{task.priority ?? "—"}</TableCell>
                                    <TableCell>
                                        <Button>Modifier</Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    );
}
