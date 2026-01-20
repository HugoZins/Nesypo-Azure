import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Pagination } from "@/components/ui/pagination";
import { ProgressCell } from "./ProgressCell";
import { TodoActionsMenu } from "./TodoActionsMenu";
import { TodoList } from "@/types/todo";
import {useState} from "react";


export function TodoTable({ data }: { data: TodoList[] }) {
    const [page, setPage] = useState(1);
    const pageSize = 10;

    const start = (page - 1) * pageSize;
    const pageData = data.slice(start, start + pageSize);

    return (
        <div className="space-y-4">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Nom</TableHead>
                        <TableHead>Progress</TableHead>
                        <TableHead>Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    {pageData.map(todo => (
                        <TableRow key={todo.id}>
                            <TableCell>{todo.name}</TableCell>
                            <TableCell>
                                <ProgressCell progress={todo.progress} />
                            </TableCell>
                            <TableCell>
                                <TodoActionsMenu todo={todo} />
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </div>
    );
}
