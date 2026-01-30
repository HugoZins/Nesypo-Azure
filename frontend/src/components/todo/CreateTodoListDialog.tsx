"use client"

import { zodResolver } from "@hookform/resolvers/zod"
import { useState } from "react"
import { useForm } from "react-hook-form"
import type { z } from "zod"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { useCreateTodoList } from "@/hooks/todoLists/useCreateTodoList"
import { todoListSchema } from "@/lib/validation/todo"

type FormValues = z.infer<typeof todoListSchema>

export function CreateTodoListDialog() {
	const [open, setOpen] = useState(false)

	const {
		register,
		handleSubmit,
		reset,
		formState: { errors },
	} = useForm<FormValues>({
		resolver: zodResolver(todoListSchema),
	})

	const { mutateAsync, isLoading } = useCreateTodoList()

	const onSubmit = async (values: FormValues) => {
		await mutateAsync(values.title)
		reset()
		setOpen(false)
	}

	return (
		<Dialog open={open} onOpenChange={setOpen}>
			<Button onClick={() => setOpen(true)}>Créer une liste</Button>

			<DialogContent>
				<DialogHeader>
					<DialogTitle>Créer une TodoList</DialogTitle>
					<DialogDescription>Donne un nom à ta liste</DialogDescription>
				</DialogHeader>

				<form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
					<div>
						<Label>Nom</Label>
						<Input {...register("title")} placeholder="Ex : Courses" />
						{errors.title && <p className="text-red-500 text-sm">{errors.title.message}</p>}
					</div>

					<Button type="submit" disabled={isLoading}>
						{isLoading ? "Création..." : "Créer"}
					</Button>
				</form>
			</DialogContent>
		</Dialog>
	)
}
