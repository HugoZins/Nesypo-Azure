"use client";

import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api";

export default function TodoListsPage() {
  const { data } = useQuery(["todoLists"], async () => {
    return await api.get("api/todo_lists").json<any>();
  });

  return (
    <div>
      <h1>Todo Lists</h1>
      <pre>{JSON.stringify(data, null, 2)}</pre>
    </div>
  );
}
