interface ReviewAttachmentEntity {
    id: number;
    transaction_item_id: number;
    file_path: string;
    file_type: "image" | "video";
    caption?: string | null;
    created_at: string;
    updated_at: string;
}
