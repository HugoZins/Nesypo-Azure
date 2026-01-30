import { Empty } from "@/components/ui/empty"

export function EmptyState() {
	return (
		<Empty>
			<div className="font-bold text-lg">Aucune todolist</div>
			<div className="text-muted-foreground text-sm">Créez une nouvelle liste pour commencer.</div>
		</Empty>
	)
}
