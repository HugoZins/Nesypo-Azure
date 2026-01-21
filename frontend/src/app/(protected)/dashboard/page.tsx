"use client";

import { TodoListTable } from "@/components/todo/TodoListTable";
import { CreateTodoListDialog } from "@/components/todo/CreateTodoListDialog";

export default function DashboardPage() {
    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <h2 className="text-xl font-bold">Vos Todolists</h2>
                <CreateTodoListDialog />
            </div>

            <TodoListTable />
        </div>
    );
}
