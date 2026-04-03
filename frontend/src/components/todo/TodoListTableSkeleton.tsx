import { Card, CardContent, CardFooter } from "@/components/ui/card"
import { Skeleton } from "@/components/ui/skeleton"

export function TodoListTableSkeleton() {
	return (
		<div className="mx-auto max-w-7xl px-6">
			<Card>
				<CardContent className="p-4">
					{Array.from({ length: 6 }, (_, i) => (
						<div
							key={`skeleton-row-${i}`}
							className="flex items-center gap-4 border-border border-b py-3 last:border-0"
						>
							<Skeleton className="h-4 flex-1" />
							<Skeleton className="h-4 w-24" />
							<Skeleton className="h-4 w-20" />
						</div>
					))}
				</CardContent>

				<CardFooter className="flex justify-end gap-2 py-4">
					<Skeleton className="h-8 w-20" />
					<Skeleton className="h-8 w-16" />
				</CardFooter>
			</Card>
		</div>
	)
}
