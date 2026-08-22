import { createInertiaApp, type ResolvedComponent } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ReactDOMServer from 'react-dom/server';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title} - ${appName}` : appName),
        resolve: (name) =>
            resolvePageComponent<{ default: ResolvedComponent }>(
                `./pages/${name}.tsx`,
                import.meta.glob<{ default: ResolvedComponent }>(
                    './pages/**/*.tsx',
                ),
            ).then((module) => module.default),
        setup: ({ App, props }) => {
            return <App {...props} />;
        },
    }),
);
