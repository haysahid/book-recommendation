interface UserEntity {
    id: number;
    name: string;
    username: string;
    email: string;
    password?: string | null;
    phone: string | null;
    address: string | null;
    profile_photo_path: string | null;
    disabled_at: string | null;
    created_at: string | null;
    updated_at: string | null;

    // Additional attributes
    profile_photo_url: string | null;
}
