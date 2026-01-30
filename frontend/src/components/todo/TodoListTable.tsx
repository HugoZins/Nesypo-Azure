"use client"

import { flexRender, getCoreRowModel, getPaginationRowModel, useReactTable } from "@tanstack/react-table"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardFooter } from "@/components/ui/card"
import { Spinner } from "@/components/ui/spinner"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { useTodoLists } from "@/hooks/todoLists/useTodoLists"
import { columns } from "./columns"

export function TodoListTable() {
	const { data = [], isLoading } = useTodoLists()

	const table = useReactTable({
		data,
		columns,
		getCoreRowModel: getCoreRowModel(),
		getPaginationRowModel: getPaginationRowModel(),
	})

	if (isLoading) {
		return (
			<div className="flex justify-center py-10">
				<Spinner />
			</div>
		)
	}

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
											{header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
										</TableHead>
									))}
								</TableRow>
							))}
						</TableHeader>

						<TableBody>
							{table.getRowModel().rows.length ? (
								table.getRowModel().rows.map((row) => (
									<TableRow key={row.id} className="group cursor-pointer">
										{row.getVisibleCells().map((cell) => (
											<TableCell
												key={cell.id}
												className="py-2 transition-colors group-hover:bg-gray-100 dark:group-hover:bg-gray-800"
											>
												{flexRender(cell.column.columnDef.cell, cell.getContext())}
											</TableCell>
										))}
									</TableRow>
								))
							) : (
								<TableRow>
									<TableCell colSpan={columns.length} className="h-24 text-center">
										Aucune TodoList
									</TableCell>
								</TableRow>
							)}
						</TableBody>
					</Table>
				</CardContent>

				<CardFooter className="flex justify-end gap-2 py-4">
					<Button
						variant="outline"
						size="sm"
						onClick={() => table.previousPage()}
						disabled={!table.getCanPreviousPage()}
					>
						Précédent
					</Button>
					<Button variant="outline" size="sm" onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}>
						Suivant
					</Button>
				</CardFooter>
			</Card>
		</div>
	)
}
