"use client"

import { zodResolver } from "@hookform/resolvers/zod"
import { useState } from "react"
import { Controller, useForm } from "react-hook-form"
import type { z } from "zod"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { useUpdateTask } from "@/hooks/tasks/useUpdateTask"
import { TASK_PRIORITIES, taskSchema } from "@/lib/validation/task"
import type { Task } from "@/types/todo"

type FormValues = z.infer<typeof taskSchema>

export function EditTaskDialog({ task, todoListId }: { task: Task; todoListId: number }) {
	const [open, setOpen] = useState(false)

	const {
		register,
		handleSubmit,
		control,
		formState: { errors, isSubmitting },
	} = useForm<FormValues>({
		resolver: zodResolver(taskSchema),
		defaultValues: {
			title: task.title,
			priority: task.priority ?? "Moyenne",
			todoListId,
		},
	})

	const updateTask = useUpdateTask(todoListId)

	const onSubmit = async (values: FormValues) => {
		await updateTask.mutateAsync({
			id: task.id,
			data: {
				title: values.title,
				priority: values.priority,
			},
		})
		setOpen(false)
	}

	return (
		<Dialog open={open} onOpenChange={setOpen}>
			<Button size="sm" variant="outline" onClick={() => setOpen(true)}>
				Modifier
			</Button>

			<DialogContent>
				<DialogHeader>
					<DialogTitle>Modifier la tâche</DialogTitle>
				</DialogHeader>

				<form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
					<div>
						<Label>Titre</Label>
						<Input {...register("title")} />
						{errors.title && (
							<p className="text-destructive text-sm">{errors.title.message}</p>
						)}
					</div>

					<div className="space-y-1">
						<Label>Priorité</Label>
						<Controller
							name="priority"
							control={control}
							render={({ field }) => (
								<Select value={field.value} onValueChange={field.onChange}>
									<SelectTrigger>
										<SelectValue />
									</SelectTrigger>
									<SelectContent className="z-50 bg-background">
										{TASK_PRIORITIES.map((p) => (
											<SelectItem key={p} value={p}>{p}</SelectItem>
										))}
									</SelectContent>
								</Select>
							)}
						/>
					</div>

					<Button type="submit" disabled={isSubmitting}>
						{isSubmitting ? "Enregistrement..." : "Enregistrer"}
					</Button>
				</form>
			</DialogContent>
		</Dialog>
	)
}