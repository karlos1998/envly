export interface User {
    id: number;
    name: string;
    email: string;
    locale: 'en' | 'pl';
    email_verified_at?: string;
}

export interface SocialAccount {
    id: number;
    provider: string;
    provider_user_id: string;
    username: string | null;
    name: string | null;
    email: string | null;
    avatar_url: string | null;
    connected_at: string | null;
}

export interface PasskeyCredential {
    id: string;
    alias: string | null;
    origin: string;
    created_at: string | null;
    updated_at: string | null;
}

export interface LocaleOption {
    value: 'en' | 'pl';
    label: string;
}

export interface EnvTemplateOption {
    value: string;
    label: string;
    description: string;
}

export interface EnvironmentVersion {
    id: number;
    summary: string | null;
    added_lines: number;
    removed_lines: number;
    has_content_changes: boolean;
    content: string;
    previous_content: string | null;
    created_at: string | null;
    creator: { id: number; name: string } | null;
}

export interface ProjectEnvironment {
    id: number;
    name: string;
    slug: string;
    content: string;
    access_token: string;
    masked_token: string;
    line_count: number;
    last_exported_at: string | null;
    updated_at: string | null;
    versions: EnvironmentVersion[];
}

export interface Project {
    id: number;
    name: string;
    identifier: string;
    display_name: string;
    created_at: string | null;
    updated_at: string | null;
    environments: ProjectEnvironment[];
}

export type TranslationTree = Record<string, string | TranslationTree>;

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User | null;
    };
    flash: {
        success?: string | null;
        error?: string | null;
    };
    locale: 'en' | 'pl';
    locales: LocaleOption[];
    translations: TranslationTree;
};
