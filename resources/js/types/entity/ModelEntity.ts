interface ModelEntity {
    id: number;
    filename: string;
    algorithm: string;
    n_factors: number;
    n_epochs: number;
    lr_all: number;
    reg_all: number;
    rmse: number;
    mae: number;
    min_value: number;
    max_value: number;
    reference: string;
    created_by: string;
    created_at: string;
}
