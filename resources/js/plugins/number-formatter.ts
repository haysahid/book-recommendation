export const formatCurrency = (
    value: string | number,
    options: Intl.NumberFormatOptions = {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }
): string => {
    if (!value) {
        value = 0;
    }
    if (typeof value === "string") {
        value = parseFloat(value);
    }
    return value.toLocaleString("id-ID", options);
};

export const formatNumber = (
    value: string | number,
    options: Intl.NumberFormatOptions = {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }
): string => {
    if (!value) {
        value = 0;
    }
    if (typeof value === "string") {
        value = parseFloat(value);
    }
    return value.toLocaleString("id-ID", options);
};
