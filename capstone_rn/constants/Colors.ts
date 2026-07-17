// Color palette for the admin app - Cream & Brown Premium Theme
export const Colors = {
  // Primary brand colors (Browns)
  primary: '#8B5A2B',       // Rich Brown
  primaryLight: '#A0522D',  // Sienna
  primaryDark: '#5C4033',   // Dark Brown

  // Secondary accent (Cream/Beige)
  secondary: '#D2B48C',     // Tan
  secondaryLight: '#F5DEB3', // Wheat
  secondaryDark: '#C19A6B', // Camel

  // Success / Warning / Error
  success: '#10B981',       
  successLight: '#34D399',
  successBg: 'rgba(16, 185, 129, 0.15)',

  warning: '#F59E0B',       
  warningLight: '#FBBF24',
  warningBg: 'rgba(245, 158, 11, 0.15)',

  error: '#EF4444',         
  errorLight: '#F87171',
  errorBg: 'rgba(239, 68, 68, 0.15)',

  info: '#3B82F6',          
  infoLight: '#60A5FA',
  infoBg: 'rgba(59, 130, 246, 0.15)',

  // Light theme backgrounds (Cream/White)
  background: '#FDFBF7',    // Cream White
  surface: '#FFFFFF',       // Pure White (Cards, Headers)
  surfaceLight: '#F5F5DC',  // Beige
  surfaceLighter: '#FAF0E6', // Linen
  card: '#FFFFFF',

  // Text colors (Dark Brown for contrast on light background)
  text: '#3E2723',          // Very Dark Brown / Almost Black
  textSecondary: '#5D4037', // Medium Dark Brown
  textMuted: '#8D6E63',     // Muted Brown
  textInverse: '#FFFFFF',   // White text on primary buttons

  // Border
  border: '#E7E5E4',        // Stone 200 (Light grayish brown)
  borderLight: '#F5F5F4',   // Stone 100

  // Misc
  white: '#FFFFFF',
  black: '#000000',
  transparent: 'transparent',
  overlay: 'rgba(62, 39, 35, 0.5)', // Dark brown overlay

  // Gradient pairs
  gradientPrimary: ['#8B5A2B', '#5C4033'] as const,
  gradientSuccess: ['#10B981', '#059669'] as const,
  gradientWarning: ['#F59E0B', '#D97706'] as const,
  gradientDark: ['#FDFBF7', '#F5F5DC'] as const,
};

export const Shadows = {
  small: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
    elevation: 3,
  },
  medium: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 6,
  },
  large: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.35,
    shadowRadius: 16,
    elevation: 10,
  },
  glow: (color: string) => ({
    shadowColor: color,
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.4,
    shadowRadius: 12,
    elevation: 8,
  }),
};

export const Spacing = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 20,
  xxl: 24,
  xxxl: 32,
};

export const FontSize = {
  xs: 10,
  sm: 12,
  md: 14,
  lg: 16,
  xl: 18,
  xxl: 22,
  xxxl: 28,
  display: 34,
};

export const BorderRadius = {
  sm: 6,
  md: 10,
  lg: 14,
  xl: 18,
  xxl: 24,
  full: 9999,
};
