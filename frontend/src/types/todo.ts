export type TodoList = {
    id: number;
    title: string;
    progress?: number;
};

export type Todo = {
    id: number;
    title: string;
    done: boolean;
};
