import '../css/app.css';

import { createInertiaApp, type ResolvedComponent } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';
import { LanguageProvider } from './shared/i18n/LanguageContext';
import { Toaster } from './shared/ui/Toaster';

const appName =
    import.meta.env.VITE_APP_NAME || 'Easywords - learn words easily';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent<{ default: ResolvedComponent }>(
            `./pages/${name}.tsx`,
            import.meta.glob<{ default: ResolvedComponent }>(
                './pages/**/*.tsx',
            ),
        ).then((module) => module.default),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <StrictMode>
                <LanguageProvider>
                    <App {...props} />
                    <Toaster />
                </LanguageProvider>
            </StrictMode>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js')
            .then((registration) => {
                console.log('SW registered:', registration);
            })
            .catch((error) => {
                console.log('SW registration failed:', error);
            });
    });
}
