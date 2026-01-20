"use client"

import { useEffect, useState } from "react";
import { TodoTable } from "./components/TodoTable";
import { EmptyState } from "./components/EmptyState";
import { CreateTodoDialog } from "./components/CreateTodoDialog";
import { Skeleton } from "@/components/ui/skeleton";
import { Spinner } from "@/components/ui/spinner";
import {TodoList} from "@/types/todo";

export default function DashboardPage() {
    const [todos, setTodos] = useState<TodoList[] | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch("/api/todolists")
            .then(res => res.json())
            .then(data => {
                setTodos(data);
            })
            .finally(() => setLoading(false));
    }, []);

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <Spinner />
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <h2 className="text-xl font-bold">Vos Todolists</h2>
                <CreateTodoDialog />
            </div>

            {todos?.length ? (
                <TodoTable data={todos} />
            ) : (
                <EmptyState />
            )}
        </div>
    );
}
