"use client";

import * as React from "react";
import {Button} from "@/components/ui/button";
import {Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter} from "@/components/ui/dialog";
import {Input} from "@/components/ui/input";
import {useUpdateTodoList} from "@/hooks/todoLists/useUpdateTodoList";
import {TodoList} from "@/types/todo";

interface EditTodoListDialogProps {
    todoList: TodoList;
}

export function EditTodoListDialog({todoList}: EditTodoListDialogProps) {
    const [open, setOpen] = React.useState(false);
    const [title, setTitle] = React.useState(todoList.title);

    const updateTodoList = useUpdateTodoList();

    const handleSave = (e: React.FormEvent) => {
        e.preventDefault();

        updateTodoList.mutate({
            id: todoList.id,
            data: {title},
        });

        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <Button variant="outline" onClick={() => setOpen(true)}>
                Modifier
            </Button>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Modifier la TodoList</DialogTitle>
                </DialogHeader>

                {/* 👇 Ajout du form */}
                <form onSubmit={handleSave} className="space-y-4">
                    <Input
                        value={title}
                        onChange={(e) => setTitle(e.target.value)}
                        placeholder="Titre de la TodoList"
                    />

                    <DialogFooter>
                        <Button variant="outline" onClick={() => setOpen(false)}>
                            Annuler
                        </Button>
                        <Button type="submit">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
