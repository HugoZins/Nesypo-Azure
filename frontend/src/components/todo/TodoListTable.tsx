"use client"

import { flexRender, getCoreRowModel, useReactTable } from "@tanstack/react-table"
import { useRouter } from "next/navigation"
import { useMemo, useState } from "react"
import { getColumns } from "@/components/todo/columns"
import { TodoListTableSkeleton } from "@/components/todo/TodoListTableSkeleton"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardFooter } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { useTodoLists } from "@/hooks/todoLists/useTodoLists"
import { useAuthStore } from "@/stores/useAuthStore"

const LIMIT = 10

export function TodoListTable() {
	const router = useRouter()
	const [page, setPage] = useState(1)
	const { data, isLoading } = useTodoLists(page, LIMIT)

	const todoLists = data?.data ?? []
	const totalPages = data?.pages ?? 1
	const total = data?.total ?? 0
	const isAdmin = useAuthStore((s) => s.user?.roles?.includes("ROLE_ADMIN") ?? false)

	const filteredColumns = useMemo(() => getColumns(isAdmin), [isAdmin])

	const table = useReactTable({
		data: todoLists,
		columns: filteredColumns,
		getCoreRowModel: getCoreRowModel(),
	})

	if (isLoading) return <TodoListTableSkeleton />

	return (
		<div className="mx-auto max-w-7xl px-6">
			<Card>
				<CardContent className="p-0">
					<Table>
						<TableHeader>
							{table.getHeaderGroups().map((headerGroup) => (
								<TableRow key={headerGroup.id}>
									{headerGroup.headers.map((header) => (
										<TableHead key={header.id}>
											{flexRender(header.column.columnDef.header, header.getContext())}
										</TableHead>
									))}
								</TableRow>
							))}
						</TableHeader>

						<TableBody>
							{table.getRowModel().rows.length === 0 ? (
								<TableRow>
									<TableCell colSpan={filteredColumns.length} className="py-6 text-center text-muted-foreground">
										Aucune todolist
									</TableCell>
								</TableRow>
							) : (
								table.getRowModel().rows.map((row) => (
									<TableRow
										key={row.id}
										className="cursor-pointer group-hover:bg-muted/50"
										onClick={() => router.push(`/dashboard/todo-lists/${row.original.id}`)}
									>
										{row.getVisibleCells().map((cell) => (
											<TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
										))}
									</TableRow>
								))
							)}
						</TableBody>
					</Table>
				</CardContent>

				<CardFooter className="flex items-center justify-between py-4">
					<span className="text-sm text-muted-foreground">
						{total} liste{total > 1 ? "s" : ""} au total
					</span>

					<div className="flex items-center gap-2">
						<Button
							variant="outline"
							size="sm"
							onClick={() => setPage((p) => Math.max(1, p - 1))}
							disabled={page === 1}
						>
							Précédent
						</Button>
						<span className="text-sm text-muted-foreground">
							Page {page} / {totalPages}
						</span>
						<Button
							variant="outline"
							size="sm"
							onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
							disabled={page === totalPages || totalPages === 0}
						>
							Suivant
						</Button>
					</div>
				</CardFooter>
			</Card>
		</div>
	)
}
