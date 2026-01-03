interface BookReviewEntity {
    id: number;
    user_id: number;
    rating: number | null;
    review: string | null;
    reviewed_at: string | null;
    created_at: string | null;
    user: UserEntity | null;
}
