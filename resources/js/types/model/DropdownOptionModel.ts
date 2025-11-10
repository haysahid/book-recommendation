interface DropdownOptionModel<T> {
    label: string;
    value: T;
    description?: string | null;
    icon?: string | null;
    hexCode?: string | null;
    disabled?: boolean | null;
}
