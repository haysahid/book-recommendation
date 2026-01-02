interface UserRecommendBookModel {
    user_id: number;
    strategy: string;
    results: BookEntity[];
}
