interface PaginationModel<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    next_page_url: string | null;
    last_page: number;
    last_page_url: string;
    per_page: number;
    total: number;
    links: Array<{ url: string; label: string; active: boolean }>;
    to: number;
    from: number;
}
