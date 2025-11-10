interface CategoryModel {
    title: string;
    slug: string;
    image: string | null;
    subcategory: CategoryModel[];
}
