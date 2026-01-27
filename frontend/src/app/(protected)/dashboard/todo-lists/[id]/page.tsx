"use client";

import {useParams} from "next/navigation";
import {useTodoList} from "@/hooks/todoLists/useTodoList";
import {TodoListTasks} from "@/components/todo/TodoListTasks";
import {CreateTaskDialog} from "@/components/todo/CreateTaskDialog";
import {Spinner} from "@/components/ui/spinner";

export default function TodoListPage() {
    const {id} = useParams();
    const todoListId = Number(id);

    const {data: todoList, isLoading, isError} = useTodoList(todoListId);

    if (isLoading) {
        return (
            <div className="flex justify-center items-center h-64">
                <Spinner/>
            </div>
        );
    }

    if (isError || !todoList) {
        return <div>Erreur lors du chargement de la TodoList</div>;
    }

    return (
        <div className="space-y-6">
            {/* HEADER PAGE */}
            <div className="flex items-center justify-between">
                <h2 className="text-xl font-bold">{todoList.title}</h2>
                <CreateTaskDialog todoListId={todoList.id}/>
            </div>

            {/* TASKS */}
            <TodoListTasks todoList={todoList}/>
        </div>
    );
}
