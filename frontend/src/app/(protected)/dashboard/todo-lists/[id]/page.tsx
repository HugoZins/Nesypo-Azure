"use client";

import {useParams} from "next/navigation";
import {useTodoList} from "@/hooks/todoLists/useTodoList";
import {TodoListTasks} from "@/components/todo/TodoListTasks";
import {Spinner} from "@/components/ui/spinner";

export default function TodoListPage() {
    const {id} = useParams();

    const {data: todoList, isLoading, isError} = useTodoList(id);

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

    return <TodoListTasks todoList={todoList}/>;
}
