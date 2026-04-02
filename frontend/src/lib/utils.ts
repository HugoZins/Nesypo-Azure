import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
	return twMerge(clsx(inputs))
}

export function getProgressColor(progress: number): string {
	if (progress === 100) return "bg-green-500"
	if (progress >= 67) return "bg-blue-500"
	if (progress >= 34) return "bg-amber-500"
	return "bg-red-500"
}