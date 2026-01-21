"use client";

import {useTodoLists} from "@/hooks/todoLists/useTodoLists";
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow} from "@/components/ui/table";
import {Progress} from "@/components/ui/progress";
import {Empty, EmptyTitle, EmptyDescription} from "@/components/ui/empty";
import {Spinner} from "@/components/ui/spinner";

export function TodoListTable() {
    const {data, isLoading} = useTodoLists();

    if (isLoading) {
        return (
            <div className="flex justify-center items-center h-64">
                <Spinner/>
            </div>
        );
    }

    if (!data?.length) {
        return (
            <Empty>
                <EmptyTitle>Aucune TodoList</EmptyTitle>
                <EmptyDescription>Créez une liste pour commencer.</EmptyDescription>
            </Empty>
        );
    }

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Nom</TableHead>
                    <TableHead>Progression</TableHead>
                    <TableHead>Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                {data.map((list) => (
                    <TableRow key={list.id}>
                        <TableCell>{list.title}</TableCell>
                        <TableCell>
                            <Progress value={list.progress} className="w-40"/>
                        </TableCell>
                        <TableCell>
                            <a href={`/dashboard/todo-lists/${list.id}`}>
                                <span className="text-blue-500 underline">Voir</span>
                            </a>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
