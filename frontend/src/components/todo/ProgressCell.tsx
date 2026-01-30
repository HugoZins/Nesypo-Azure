import { Progress } from "@/components/ui/progress"

export function ProgressCell({ progress }: { progress: number }) {
	return <Progress value={progress} className="w-full" />
}
