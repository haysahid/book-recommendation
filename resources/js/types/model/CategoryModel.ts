interface CategoryModel {
    title: string;
    slug: string;
    image: string | null;
    subcategory: CategoryModel[];

    // Additional attributes
    count_loaded_books?: number;
    total_data_books?: number;
}
