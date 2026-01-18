/// <reference types="vite/client" />
/// <reference types="vite-plugin-pwa/client" />

declare global {
    interface Window {
        route: (
            name: string,
            params?:
                | Record<string, string | number | boolean | undefined>
                | string
                | number
                | undefined,
            absolute?: boolean,
        ) => string;
    }
}

declare function route(
    name: string,
    params?:
        | Record<string, string | number | boolean | undefined>
        | string
        | number
        | undefined,
    absolute?: boolean,
): string;
