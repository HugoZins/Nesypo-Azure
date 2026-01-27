"use client";

import {useTasks} from "@/hooks/tasks/useTasks";
import {useUpdateTask} from "@/hooks/tasks/useUpdateTask";
import {TodoList} from "@/types/todo";

import {Card, CardContent, CardHeader, CardTitle} from "@/components/ui/card";
import {Progress} from "@/components/ui/progress";
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow,} from "@/components/ui/table";
import {Button} from "@/components/ui/button";
import {Separator} from "@/components/ui/separator";
import {Checkbox} from "@/components/ui/checkbox";
import {Spinner} from "@/components/ui/spinner";
import {EditTaskDialog} from "@/components/todo/EditTaskDialog";
import {DeleteTaskAlert} from "@/components/todo/DeleteTaskAlert";
import {EditTodoListDialog} from "@/components/todo/EditTodoListDialog";

interface TodoListTasksProps {
    todoList: TodoList;
}

export function TodoListTasks({todoList}: { todoList: TodoList }) {
    const {data: tasks = [], isLoading} = useTasks(todoList.id);
    const updateTask = useUpdateTask(todoList.id);

    if (isLoading) {
        return (
            <div className="flex justify-center items-center h-64">
                <Spinner/>
            </div>
        );
    }

    const completed = tasks.filter((task) => task.done).length;
    const total = tasks.length;
    const progress = total === 0 ? 0 : Math.round((completed / total) * 100);

    return (
        <div className="space-y-6">
            {/* HEADER */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex justify-between items-center">
                        <span>{todoList.title}</span>
                        <EditTodoListDialog todoList={todoList} />
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
                    {tasks.length === 0 ? (
                        <div className="text-sm text-muted-foreground text-center py-6">
                            Aucune tâche pour cette todolist
                        </div>
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
                                        <TableCell className="flex items-center gap-2">
                                            <Checkbox
                                                checked={task.done}
                                                onCheckedChange={(checked) =>
                                                    updateTask.mutate({
                                                        id: task.id,
                                                        data: {done: checked === true},
                                                    })
                                                }
                                            />

                                            <span
                                                className={
                                                    task.done ? "line-through text-muted-foreground" : ""
                                                }
                                            >
                                                {task.title}
                                            </span>
                                        </TableCell>

                                        <TableCell>
                                            {task.done ? "✔️ Fait" : "⏳ À faire"}
                                        </TableCell>

                                        <TableCell>
                                            {task.priority ?? "—"}
                                        </TableCell>

                                        <TableCell>
                                            <EditTaskDialog task={task} todoListId={todoList.id}/>
                                            <DeleteTaskAlert task={task} todoListId={todoList.id}/>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}
