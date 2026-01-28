import {useForm} from "react-hook-form";
import {useState} from "react";
import {useUpdateTodoList} from "@/hooks/todoLists/useUpdateTodoList";
import {todoListSchema} from "@/lib/validation/todo";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter
} from "@/components/ui/dialog";
import {Button} from "@/components/ui/button";
import {Input} from "@/components/ui/input";
import {zodResolver} from "@hookform/resolvers/zod";
import {z} from "zod";

type FormValues = z.infer<typeof todoListSchema>;

export function EditTodoListDialog({todoList}: EditTodoListDialogProps) {
    const [open, setOpen] = useState(false);

    const {register, handleSubmit, formState: {errors}} = useForm<FormValues>({
        resolver: zodResolver(todoListSchema),
        defaultValues: {title: todoList.title},
    });

    const updateTodoList = useUpdateTodoList();

    const onSubmit = (values: FormValues) => {
        updateTodoList.mutate({id: todoList.id, data: {title: values.title}});
        setOpen(false);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <Button variant="outline" onClick={() => setOpen(true)}>Modifier</Button>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Modifier la TodoList</DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
                    <Input {...register("title")} />
                    {errors.title && (
                        <p className="text-red-500 text-sm">{errors.title.message}</p>
                    )}

                    <DialogFooter>
                        <Button variant="outline" onClick={() => setOpen(false)}>Annuler</Button>
                        <Button type="submit">Enregistrer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
