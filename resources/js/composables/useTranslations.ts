import { usePage } from '@inertiajs/vue3';
import type { PageProps, TranslationTree } from '@/types';

export function useTranslations() {
    const page = usePage<PageProps>();

    const t = (key: string): string => {
        const value = key.split('.').reduce<TranslationTree | string | undefined>((carry, segment) => {
            if (typeof carry === 'object' && carry !== null) {
                return carry[segment];
            }

            return undefined;
        }, page.props.translations);

        return typeof value === 'string' ? value : key;
    };

    return { t };
}
