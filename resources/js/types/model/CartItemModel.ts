interface CartItemModel {
    store_id: number;
    book_id: number;
    quantity: number;
    image: string | null;
    created_at: string;
    updated_at?: string | null;

    // Additional properties for UI state management
    selected: boolean;
    showDeleteConfirmation?: boolean | null;

    // Relationships
    book: BookEntity | null;
    store: StoreEntity | null;
}
