"use client";

import {useTodoListTasks} from "@/hooks/tasks/useTodoListTasks";
import {TodoList} from "@/types/todo";

import {Card, CardContent, CardHeader, CardTitle} from "@/components/ui/card";
import {Progress} from "@/components/ui/progress";
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow} from "@/components/ui/table";
import {Button} from "@/components/ui/button";
import {Separator} from "@/components/ui/separator";
import {Checkbox} from "@/components/ui/checkbox";
import {Spinner} from "@/components/ui/spinner";

type Task = {
    id: number;
    title: string;
    done: boolean;
    priority?: string;
    todoList: { id: number };
};

export function TodoListTasks({todoList}: { todoList: TodoList }) {
    const {data: tasks = [], isLoading} = useTodoListTasks(todoList.id);

    if (isLoading) {
        return (
            <div className="flex justify-center items-center h-64">
                <Spinner/>
            </div>
        );
    }

    const tasksForList = tasks.filter((t) => t.todoList.id === todoList.id);

    const completed = tasksForList.filter((t) => t.done).length;
    const total = tasksForList.length;
    const progress = total === 0 ? 0 : Math.round((completed / total) * 100);

    return (
        <div className="space-y-6">
            {/* HEADER */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex justify-between items-center">
                        <span>{todoList.title}</span>
                        <Button>Modifier</Button>
                    </CardTitle>

                    <div className="mt-2">
                        <Progress value={progress}/>
                        <div className="text-sm text-muted-foreground mt-1">
                            {completed}/{total} tâches terminées
                        </div>
                    </div>
                </CardHeader>
            </Card>

            <Separator/>

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
                                    <TableCell className="flex items-center gap-2">
                                        <Checkbox checked={task.done}/>
                                        <span className={task.done ? "line-through" : ""}>
                      {task.title}
                    </span>
                                    </TableCell>

                                    <TableCell>
                                        {task.done ? "✔️ Fait" : "⏳ À faire"}
                                    </TableCell>

                                    <TableCell>{task.priority ?? "—"}</TableCell>

                                    <TableCell>
                                        <Button variant="outline" size="sm">
                                            Modifier
                                        </Button>
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
