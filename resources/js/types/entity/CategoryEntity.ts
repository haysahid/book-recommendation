interface CategoryEntity {
    id: number;
    title: string;
    slug: string;
    image: string | null;
    parent_id: number | null;
    sub_categories: CategoryEntity[];
}
