/**
 * Copies text to clipboard with fallback for non-secure contexts
 */
export async function copyToClipboard(text: string): Promise<boolean> {
    // Modern Clipboard API (works in secure contexts)
    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            console.error('Failed to copy with Clipboard API:', err);
        }
    }

    // Fallback method for non-secure contexts
    return fallbackCopyToClipboard(text);
}

function fallbackCopyToClipboard(text: string): boolean {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);

    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        document.body.removeChild(textArea);
        return successful;
    } catch (err) {
        console.error('Fallback copy failed:', err);
        document.body.removeChild(textArea);
        return false;
    }
}
