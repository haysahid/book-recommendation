interface BookModel {
    product_meta_id: number | null;
    title: string | null;
    image: string | null;
    slug: string | null;
    author: string | null;
    final_price: number | null;
    slice_price: number | null;
    discount: number | null;
    is_oos: boolean | null;
    sku: string | null;
    category_slugs: string | null;
    format: string | null;
    applied_promo_slug: string | null;
    store_name: string | null;
    isbn: string | null;
    warehouse_slug: string | null;
    warehouse_id: number | null;
}
