"use client"

import { notFound, useParams } from "next/navigation"
import { useEffect } from "react"
import { TodoListTasks } from "@/components/todo/TodoListTasks"
import { TodoListTasksSkeleton } from "@/components/todo/TodoListTasksSkeleton"
import { useTodoList } from "@/hooks/todoLists/useTodoList"

export default function TodoListPage() {
	const { id } = useParams()
	const todoListId = Number(id)

	const { data: todoList, isLoading, isError } = useTodoList(Number.isNaN(todoListId) ? undefined : todoListId)

	useEffect(() => {
		if (todoList?.title) {
			document.title = `${todoList.title} | Nesypo`
		}
	}, [todoList?.title])

	if (isLoading) {
		return <TodoListTasksSkeleton />
	}

	if (isError || !todoList) {
		notFound()
	}

	return (
		<div className="space-y-6">
			<h2 className="font-bold text-xl">{todoList.title}</h2>
			<TodoListTasks todoList={todoList} />
		</div>
	)
}
