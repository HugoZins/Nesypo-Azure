"use client"

import { zodResolver } from "@hookform/resolvers/zod"
import { useEffect, useRef, useState } from "react"
import { Controller, useFieldArray, useForm } from "react-hook-form"
import { z } from "zod"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { useCreateTask } from "@/hooks/tasks/useCreateTask"
import { useCreateTodoList } from "@/hooks/todoLists/useCreateTodoList"
import { TASK_PRIORITIES } from "@/lib/validation/task"
import { todoListSchema } from "@/lib/validation/todo"

const createTodoListWithTasksSchema = todoListSchema.extend({
	tasks: z.array(
		z.object({
			title: z.string().min(3, "Le titre doit contenir au moins 3 caractères"),
			priority: z.enum(TASK_PRIORITIES, {
				error: () => ({ message: "Veuillez sélectionner une priorité" }),
			}),
		})
	).optional(),
})

type FormValues = z.infer<typeof createTodoListWithTasksSchema>

export function CreateTodoListDialog() {
	const [open, setOpen] = useState(false)
	const lastTaskRef = useRef<HTMLDivElement>(null)
	const [shouldScroll, setShouldScroll] = useState(false)

	const {
		register,
		handleSubmit,
		reset,
		control,
		formState: { errors, isSubmitting },
	} = useForm<FormValues>({
		resolver: zodResolver(createTodoListWithTasksSchema),
		defaultValues: { title: "", tasks: [] },
	})

	const { fields, append, remove } = useFieldArray({
		control,
		name: "tasks",
	})

	const { mutateAsync: createTodoList } = useCreateTodoList()
	const { mutateAsync: createTask } = useCreateTask(0)

	useEffect(() => {
		if (shouldScroll && lastTaskRef.current) {
			lastTaskRef.current.scrollIntoView({ behavior: "smooth", block: "start" })
			setShouldScroll(false)
		}
	}, [fields.length, shouldScroll])

	const handleAddTask = () => {
		append({ title: "", priority: "Moyenne" })
		setShouldScroll(true)
	}

	const onSubmit = async (values: FormValues) => {
		const todoList = await createTodoList(values.title)

		if (values.tasks && values.tasks.length > 0) {
			await Promise.all(
				values.tasks.map((task) =>
					createTask({
						title: task.title,
						priority: task.priority,
						todoListId: todoList.id,
					})
				)
			)
		}

		reset()
		setOpen(false)
	}

	const handleClose = (isOpen: boolean) => {
		if (!isOpen) reset()
		setOpen(isOpen)
	}

	return (
		<Dialog open={open} onOpenChange={handleClose}>
			<Button onClick={() => setOpen(true)}>Créer une liste</Button>

			<DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
				<DialogHeader>
					<DialogTitle>Créer une TodoList</DialogTitle>
					<DialogDescription>
						Donne un nom à ta liste et ajoute des tâches optionnellement.
					</DialogDescription>
				</DialogHeader>

				<form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
					<div className="space-y-1">
						<Label>Nom de la liste</Label>
						<Input {...register("title")} placeholder="Ex : Courses" />
						{errors.title && (
							<p className="text-destructive text-sm">{errors.title.message}</p>
						)}
					</div>

					<div className="space-y-3">
						<Label>Tâches</Label>

						{fields.length === 0 && (
							<p className="text-sm text-muted-foreground">
								Aucune tâche — ajoutez en ici ou par la suite.
							</p>
						)}

						{fields.map((field, index) => {
							const isLast = index === fields.length - 1
							return (
								<div
									key={field.id}
									ref={isLast ? lastTaskRef : null}
									className="rounded-lg border border-border p-3 space-y-3"
								>
									<div className="flex items-center justify-between">
                                        <span className="text-sm font-medium text-muted-foreground">
                                            Tâche {index + 1}
                                        </span>
										<Button
											type="button"
											variant="ghost"
											size="sm"
											onClick={() => remove(index)}
											className="text-destructive hover:text-destructive"
										>
											Supprimer
										</Button>
									</div>

									<div className="space-y-1">
										<Label>Titre</Label>
										<Input
											{...register(`tasks.${index}.title`)}
											placeholder="Ex : Acheter du lait"
										/>
										{errors.tasks?.[index]?.title && (
											<p className="text-destructive text-sm">
												{errors.tasks[index].title.message}
											</p>
										)}
									</div>

									<div className="space-y-1">
										<Label>Priorité</Label>
										<Controller
											name={`tasks.${index}.priority`}
											control={control}
											render={({ field: f }) => (
												<Select value={f.value} onValueChange={f.onChange}>
													<SelectTrigger>
														<SelectValue placeholder="Choisir une priorité" />
													</SelectTrigger>
													<SelectContent className="z-50 bg-background">
														{TASK_PRIORITIES.map((p) => (
															<SelectItem key={p} value={p}>{p}</SelectItem>
														))}
													</SelectContent>
												</Select>
											)}
										/>
										{errors.tasks?.[index]?.priority && (
											<p className="text-destructive text-sm">
												{errors.tasks[index].priority.message}
											</p>
										)}
									</div>
								</div>
							)
						})}

						<Button
							type="button"
							variant="outline"
							size="sm"
							className="w-full"
							onClick={handleAddTask}
						>
							+ Ajouter une tâche
						</Button>
					</div>

					<Button type="submit" className="w-full" disabled={isSubmitting}>
						{isSubmitting ? "Création..." : "Créer la liste"}
					</Button>
				</form>
			</DialogContent>
		</Dialog>
	)
}