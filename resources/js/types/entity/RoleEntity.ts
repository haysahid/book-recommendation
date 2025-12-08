interface RoleEntity {
    id: number;
    name: string;
    guard_name: string;
    created_at: string | null;
    updated_at: string | null;

    // Pivot
    pivot?: {
        model_id: number;
        role_id: number;
        model_type: string;
    };
}
