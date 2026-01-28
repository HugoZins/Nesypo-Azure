"use client";

import {useState} from "react";
import {Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,} from "@/components/ui/dialog";
import {Button} from "@/components/ui/button";
import {Input} from "@/components/ui/input";
import {Label} from "@/components/ui/label";
import {useForm} from "react-hook-form";
import {zodResolver} from "@hookform/resolvers/zod";
import {todoListSchema} from "@/lib/validation/todo";
import {useCreateTodoList} from "@/hooks/todoLists/useCreateTodoList";
import {z} from "zod";

type FormValues = z.infer<typeof todoListSchema>;

export function CreateTodoListDialog() {
    const [open, setOpen] = useState(false);

    const {register, handleSubmit, reset, formState: {errors}} = useForm<FormValues>({
        resolver: zodResolver(todoListSchema),
    });

    const {mutateAsync, isLoading} = useCreateTodoList();

    const onSubmit = async (values: FormValues) => {
        await mutateAsync(values.title);
        reset();
        setOpen(false);
    };

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
                        <Input {...register("title")} placeholder="Ex : Courses"/>
                        {errors.title && (
                            <p className="text-red-500 text-sm">{errors.title.message}</p>
                        )}
                    </div>

                    <Button type="submit" disabled={isLoading}>
                        {isLoading ? "Création..." : "Créer"}
                    </Button>
                </form>
            </DialogContent>
        </Dialog>
    );
}
