// Color palette for the admin app - dark premium theme
export const Colors = {
  // Primary brand colors
  primary: '#6366F1',       // Indigo 500
  primaryLight: '#818CF8',  // Indigo 400
  primaryDark: '#4338CA',   // Indigo 700

  // Secondary accent
  secondary: '#06B6D4',     // Cyan 500
  secondaryLight: '#22D3EE',
  secondaryDark: '#0891B2',

  // Success / Warning / Error
  success: '#10B981',       // Emerald 500
  successLight: '#34D399',
  successBg: 'rgba(16, 185, 129, 0.15)',

  warning: '#F59E0B',       // Amber 500
  warningLight: '#FBBF24',
  warningBg: 'rgba(245, 158, 11, 0.15)',

  error: '#EF4444',         // Red 500
  errorLight: '#F87171',
  errorBg: 'rgba(239, 68, 68, 0.15)',

  info: '#3B82F6',          // Blue 500
  infoLight: '#60A5FA',
  infoBg: 'rgba(59, 130, 246, 0.15)',

  // Dark theme backgrounds
  background: '#0F172A',    // Slate 900
  surface: '#1E293B',       // Slate 800
  surfaceLight: '#334155',  // Slate 700
  surfaceLighter: '#475569', // Slate 600
  card: '#1E293B',

  // Text colors
  text: '#F8FAFC',          // Slate 50
  textSecondary: '#94A3B8', // Slate 400
  textMuted: '#64748B',     // Slate 500
  textInverse: '#0F172A',

  // Border
  border: '#334155',        // Slate 700
  borderLight: '#475569',

  // Misc
  white: '#FFFFFF',
  black: '#000000',
  transparent: 'transparent',
  overlay: 'rgba(0, 0, 0, 0.5)',

  // Gradient pairs
  gradientPrimary: ['#6366F1', '#8B5CF6'] as const,
  gradientSuccess: ['#10B981', '#06B6D4'] as const,
  gradientWarning: ['#F59E0B', '#EF4444'] as const,
  gradientDark: ['#1E293B', '#0F172A'] as const,
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
