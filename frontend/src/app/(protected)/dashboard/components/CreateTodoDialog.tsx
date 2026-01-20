import { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

export function CreateTodoDialog() {
    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button>Créer une liste</Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Créer une nouvelle todolist</DialogTitle>
                </DialogHeader>

                <form>
                    <Input placeholder="Nom de la liste" />
                    <DialogFooter>
                        <Button type="submit">Créer</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
