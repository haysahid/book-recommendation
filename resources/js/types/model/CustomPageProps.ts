import { PageProps } from "@inertiajs/core";

export default interface CustomPageProps extends PageProps {
    auth?: {
        user?: UserEntity;
    };
    flash?: {
        success?: string;
        [key: string]: any;
    };
    [key: string]: any;
}
