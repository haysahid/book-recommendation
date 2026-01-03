interface TransactionItemEntity {
    id: number;
    store_id: number;
    transaction_id: number;
    user_id: number;
    book_id: number;
    quantity: number;
    unit_base_price: number;
    unit_discount_type: string;
    unit_discount: number;
    unit_final_price: number;
    subtotal: number;
    fullfillment_status: string;
    rating?: number | null;
    review?: string | null;
    reviewed_at?: string | null;
    created_at: string;
    updated_at: string;

    // Additional attributes
    image?: string | null;

    // Relationships
    book?: BookEntity | null;
    transaction?: TransactionEntity | null;
    store?: StoreEntity | null;
    user?: UserEntity | null;
    attachments?: ReviewAttachmentEntity[] | null;
}
