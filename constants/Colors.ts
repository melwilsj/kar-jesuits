// Define base colors (optional, but can help consistency)
const tintColorLight = '#1E40AF'; // Jesuit Blue
const tintColorDark = '#A5B4FC'; // Lighter blue for dark mode

const grayLight = {
  50: '#F9FAFB',
  100: '#F3F4F6',
  200: '#E5E7EB',
  300: '#D1D5DB',
  400: '#9CA3AF',
  500: '#6B7280',
  600: '#4B5563',
  700: '#374151',
  800: '#1F2937',
  900: '#111827',
};

const grayDark = {
  50: '#111827', // Darker shades for dark mode backgrounds/elements
  100: '#1F2937',
  200: '#374151',
  300: '#4B5563',
  400: '#6B7280',
  500: '#9CA3AF',
  600: '#D1D5DB',
  700: '#E5E7EB',
  800: '#F3F4F6',
  900: '#F9FAFB', // Lighter shades for dark mode text/elements
};


export const Color = {
  primary: '#1E40AF', // Jesuit blue
  secondary: '#6B7280',
  background: '#FFFFFF',
  text: '#111827',
  error: '#DC2626',
  warning: '#F59E0B',
  success: '#059669',
  border: '#E5E7EB',
  gray: {
    50: '#F9FAFB',
    100: '#F3F4F6',
    200: '#E5E7EB',
    300: '#D1D5DB',
    400: '#9CA3AF',
    500: '#6B7280',
    600: '#4B5563',
    700: '#374151',
    800: '#1F2937',
    900: '#111827',
  },
  blue: {
    500: '#1E40AF',
  },
  green: {
    500: '#059669',
  },
  orange: {
    500: '#F59E0B',
  },
  white: '#FFFFFF',
  purple: {
    500: '#9333EA',
  },
  teal: {
    500: '#0D9488',
  },
};

export default {
  light: {
    text: grayLight[900],
    textSecondary: grayLight[500],
    background: '#FFFFFF',
    card: grayLight[50],
    border: grayLight[200],
    tint: tintColorLight,
    primary: tintColorLight,
    secondary: grayLight[500],
    icon: grayLight[600],
    tabIconDefault: grayLight[400],
    tabIconSelected: tintColorLight,
    buttonBackground: tintColorLight,
    buttonText: '#FFFFFF',
    inputBackground: grayLight[100],
    inputPlaceholder: grayLight[400],
    error: '#DC2626',
    warning: '#F59E0B',
    success: '#059669',
    link: tintColorLight,
    separator: grayLight[200],
    gray100: grayLight[100],
    gray200: grayLight[200],
    gray300: grayLight[300],
    gray400: grayLight[400],
    gray500: grayLight[500],
    gray600: grayLight[600],
    gray700: grayLight[700],
    gray800: grayLight[800],
    gray900: grayLight[900],
    // Add other semantic colors as needed
  },
  dark: {
    text: grayDark[900], // Often white or light gray
    textSecondary: grayDark[500],
    background: grayDark[100], // Often near-black or dark gray
    card: grayDark[200],
    border: grayDark[300],
    tint: tintColorDark,
    primary: tintColorDark,
    secondary: grayDark[500],
    icon: grayDark[600],
    tabIconDefault: grayDark[400],
    tabIconSelected: tintColorDark,
    buttonBackground: tintColorDark,
    buttonText: grayDark[100], // Text color for dark buttons
    inputBackground: grayDark[300],
    inputPlaceholder: grayDark[500],
    error: '#F87171', // Lighter red for dark mode
    warning: '#FCD34D', // Lighter orange
    success: '#34D399', // Lighter green
    link: tintColorDark,
    separator: grayDark[300],
    gray100: grayDark[100],
    gray200: grayDark[200],
    gray300: grayDark[300],
    gray400: grayDark[400],
    gray500: grayDark[500],
    gray600: grayDark[600],
    gray700: grayDark[700],
    gray800: grayDark[800],
    gray900: grayDark[900],
    // Add other semantic colors as needed
  },
}; 