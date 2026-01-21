"use client";

import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useForm } from "react-hook-form";
import { api } from "@/lib/api";

type FormValues = {
    name: string;
};

export function CreateTodoListDialog() {
    const [open, setOpen] = useState(false);

    const { register, handleSubmit } = useForm<FormValues>();

    const onSubmit = async (values: FormValues) => {
        await api.post("api/todo_lists", { json: values }).json();
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
                        <Input {...register("name")} placeholder="Ex : Courses" />
                    </div>

                    <Button type="submit">Créer</Button>
                </form>
            </DialogContent>
        </Dialog>
    );
}
