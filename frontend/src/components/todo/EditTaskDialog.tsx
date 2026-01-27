"use client";

import {useState} from "react";
import {useForm, Controller} from "react-hook-form";

import {Dialog, DialogContent, DialogHeader, DialogTitle,} from "@/components/ui/dialog";
import {Button} from "@/components/ui/button";
import {Input} from "@/components/ui/input";
import {Label} from "@/components/ui/label";
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue,} from "@/components/ui/select";

import {Task} from "@/types/todo";
import {useUpdateTask} from "@/hooks/tasks/useUpdateTask";

type FormValues = {
    title: string;
    priority: "low" | "medium" | "high";
};

export function EditTaskDialog({
                                   task,
                                   todoListId,
                               }: {
    task: Task;
    todoListId: number;
}) {
    const [open, setOpen] = useState(false);

    const {control, register, handleSubmit} = useForm<FormValues>({
        defaultValues: {
            title: task.title,
            priority: task.priority ?? "medium",
        },
    });

    const updateTask = useUpdateTask(todoListId);

    const onSubmit = async (values: FormValues) => {
        await updateTask.mutateAsync({
            id: task.id,
            data: {
                title: values.title,
                priority: values.priority,
            },
        });

        setOpen(false);
    };

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
                        <Input {...register("title", {required: true})} />
                    </div>

                    <div>
                        <Label>Priorité</Label>
                        <Controller
                            name="priority"
                            control={control}
                            render={({field}) => (
                                <Select value={field.value} onValueChange={field.onChange}>
                                    <SelectTrigger>
                                        <SelectValue/>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="low">Basse</SelectItem>
                                        <SelectItem value="medium">Moyenne</SelectItem>
                                        <SelectItem value="high">Haute</SelectItem>
                                    </SelectContent>
                                </Select>
                            )}
                        />
                    </div>

                    <Button type="submit">Enregistrer</Button>
                </form>
            </DialogContent>
        </Dialog>
    );
}
