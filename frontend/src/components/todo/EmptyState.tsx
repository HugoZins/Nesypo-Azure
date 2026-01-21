import {Empty} from "@/components/ui/empty";

export function EmptyState() {
    return (
        <Empty>
            <div className="text-lg font-bold">Aucune todolist</div>
            <div className="text-sm text-muted-foreground">
                Créez une nouvelle liste pour commencer.
            </div>
        </Empty>
    );
}
