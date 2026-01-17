import { TranslationStructure } from '@/shared/i18n/types';

interface WordStudyKeyboardShortcutsProps {
    t: TranslationStructure;
}

export function WordStudyKeyboardShortcuts({
    t,
}: WordStudyKeyboardShortcutsProps) {
    return (
        <div className="mt-12 rounded-xl bg-white/60 p-6 shadow-md backdrop-blur-sm dark:bg-neutral-800/60">
            <h3 className="text-primary-900 dark:text-primary-100 mb-4 text-lg font-semibold">
                ⌨️ {t.words.keyboard_shortcuts}
            </h3>
            <p className="text-primary-700 dark:text-primary-300 mb-4 text-sm">
                {t.words.shortcuts_description}
            </p>
            <div className="text-primary-600 dark:text-primary-400 grid gap-3 text-sm md:grid-cols-2">
                <ShortcutItem
                    keys={['Enter']}
                    description="Отметить как выученное"
                />
                <ShortcutItem keys={['Space']} description="Перевернуть" />
                <ShortcutItem keys={['←']} description="Предыдущее слово" />
                <ShortcutItem keys={['→']} description="Следующее слово" />
                <ShortcutItem
                    keys={['Backspace']}
                    description="Вернуться назад"
                />
                <ShortcutItem keys={['Delete']} description="Удалить слово" />
            </div>
        </div>
    );
}

function ShortcutItem({
    keys,
    description,
}: {
    keys: string[];
    description: string;
}) {
    return (
        <div className="flex items-center gap-2">
            {keys.map((key, index) => (
                <kbd
                    key={index}
                    className="rounded-md border border-neutral-300 bg-neutral-100 px-3 py-1.5 font-mono text-xs shadow-sm dark:border-neutral-700 dark:bg-neutral-900"
                >
                    {key}
                </kbd>
            ))}
            <span>{description}</span>
        </div>
    );
}
