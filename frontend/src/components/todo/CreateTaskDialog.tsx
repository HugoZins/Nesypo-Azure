"use client";

import {useState} from "react";
import {useForm, Controller} from "react-hook-form";

import {Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,} from "@/components/ui/dialog";
import {Button} from "@/components/ui/button";
import {Input} from "@/components/ui/input";
import {Label} from "@/components/ui/label";
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue,} from "@/components/ui/select";

import {useCreateTask} from "@/hooks/tasks/useCreateTask";

type FormValues = {
    title: string;
    priority: "LOW" | "MEDIUM" | "HIGH";
};

export function CreateTaskDialog({todoListId}: { todoListId: number }) {
    const [open, setOpen] = useState(false);

    const {
        register,
        handleSubmit,
        reset,
        control,
    } = useForm<FormValues>({
        defaultValues: {
            priority: "MEDIUM",
        },
    });

    const {mutateAsync, isLoading} = useCreateTask(todoListId);

    const onSubmit = async (values: FormValues) => {
        await mutateAsync({
            title: values.title,
            priority: values.priority,
            todoListId,
            done: false,
        });

        reset();
        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <Button onClick={() => setOpen(true)}>Ajouter une tâche</Button>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Nouvelle tâche</DialogTitle>
                    <DialogDescription>
                        Ajoute une tâche à cette TodoList
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
                    {/* TITRE */}
                    <div>
                        <Label>Titre</Label>
                        <Input
                            {...register("title", {required: true})}
                            placeholder="Ex : Acheter du lait"
                        />
                    </div>

                    {/* PRIORITÉ */}
                    <div>
                        <Label>Priorité</Label>
                        <Controller
                            name="priority"
                            control={control}
                            render={({field}) => (
                                <Select value={field.value} onValueChange={field.onChange}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Choisir une priorité"/>
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

                    <Button type="submit" disabled={isLoading}>
                        {isLoading ? "Création..." : "Créer"}
                    </Button>
                </form>
            </DialogContent>
        </Dialog>
    );
}
