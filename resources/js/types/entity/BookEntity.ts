interface BookEntity {
    id: number;
    title: string;
    translated_title: string | null;
    cleaned_title: string | null;
    stemmed_title: string | null;
    image: string | null;
    slug: string;
    author: string | null;
    final_price: number | null;
    slice_price: number | null;
    discount: number | null;
    is_oos: boolean;
    sku: string | null;
    format: string | null;
    store_name: string | null;
    isbn: string | null;
    created_at: string;
    updated_at: string;

    // Additional attributes
    score: number | null;
    reason: string | null;
}
