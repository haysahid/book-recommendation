interface BookDetailModel {
    title: string | null;
    slug: string | null;
    variant_name: string | null;
    variant_code: string | null;
    variant_info: string | null;
    pre_order_period: string | null;
    final_price: number | null;
    slice_price: number | null;
    discount: number | null;
    is_oos: boolean | null;
    is_only_available_offline: boolean | null;
    warehouse_code: string | null;
    is_flashsale: boolean | null;
    po_quota: number | null;
    po_total_bought: number | null;
    category: {
        title: string | null;
        slug: string | null;
    } | null;
    image:
        | {
              image: string | null;
              priority: number | null;
          }[]
        | null;
    specifications:
        | {
              label: string | null;
              value: string | null;
              url?: string | null;
          }[]
        | null;
    sku: string | null;
    isbn: string | null;
    category_slugs: string | null;
    store_name: string | null;
    applied_promo_slug: string | null;
}
