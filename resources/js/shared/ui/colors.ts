export const colors = {
    // Primary brand colors
    primary: {
        DEFAULT: '#f53003',
        hover: '#e03003',
        dark: '#FF4433',
        darkHover: '#ff5555',
    },

    // Background colors
    background: {
        DEFAULT: '#FDFDFC',
        dark: '#0a0a0a',
    },

    // Text colors
    text: {
        DEFAULT: '#1b1b18',
        dark: '#EDEDEC',
        muted: '#706f6c',
        darkMuted: '#A1A09A',
    },

    // Border colors
    border: {
        DEFAULT: '#e3e3e0',
        dark: '#3E3E3A',
        DEFAULT_LOW_OPACITY: '#19140035',
        darkHover: '#1915014a',
        darkLowOpacity: '#62605b',
    },

    // Accent colors
    accent: {
        gradientStart: '#FDFDFC',
        gradientEnd: 'white',
        darkGradientStart: '#0a0a0a',
        darkGradientEnd: '#161615',
        cardDark: '#161615',
        cardEvenDarker: '#1C1C1A',
    },
} as const;
