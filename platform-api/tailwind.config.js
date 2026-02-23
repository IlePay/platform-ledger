import preset from './vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                'primary': {
                    50: '#F0F4FF',
                    100: '#E0E9FF',
                    200: '#C7D7FE',
                    300: '#A4BCFD',
                    400: '#8199FA',
                    500: '#2D4B9E', // Main IlePay blue
                    600: '#253D82',
                    700: '#1D2F66',
                    800: '#15214A',
                    900: '#0D1331',
                    950: '#070B1A',
                },
                'secondary': {
                    50: '#FFFBEB',
                    100: '#FEF3C7',
                    200: '#FDE68A',
                    300: '#FCD34D',
                    400: '#FBBF24',
                    500: '#F9B233', // Main IlePay yellow
                    600: '#D97706',
                    700: '#B45309',
                    800: '#92400E',
                    900: '#78350F',
                    950: '#451A03',
                },
            },
        },
    },
}
