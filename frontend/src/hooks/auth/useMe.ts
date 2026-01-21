import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";

export function useMe() {
    return useQuery({
        queryKey: ["me"],
        queryFn: () => api.get("api/me").json(),
        staleTime: 1000 * 60 * 5, // 5 minutes
        retry: false,
    });
}
